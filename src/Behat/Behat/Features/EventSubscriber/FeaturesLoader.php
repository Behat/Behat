<?php

namespace Behat\Behat\Features\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Features\Event\FeaturesCarrierEvent;
use Behat\Behat\Features\Loader\LoaderInterface;
use Behat\Behat\Features\SuitedFeature;
use Behat\Behat\Suite\Event\SuitesCarrierEvent;
use Behat\Behat\Suite\SuiteInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Features loader event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoader extends DispatchingService implements EventSubscriberInterface
{
    /**
     * @var LoaderInterface[]
     */
    private $loaders = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::LOAD_FEATURES => array('loadFeatures', 0)
        );
    }

    /**
     * Registers loader.
     *
     * @param LoaderInterface $loader
     */
    public function registerLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Loads appropriate features into event using loaders.
     *
     * @param FeaturesCarrierEvent $event
     */
    public function loadFeatures(FeaturesCarrierEvent $event)
    {
        $locator = $event->getLocator();
        $suitesProvider = new SuitesCarrierEvent();
        $this->dispatch(EventInterface::LOAD_SUITES, $suitesProvider);

        if ($event->hasSuiteName()) {
            $suite = $suitesProvider->getSuite($event->getSuiteName());
            array_map(array($event, 'addFeature'), $this->loadSuiteFeatures($suite, $locator));

            return;
        }

        foreach ($suitesProvider->getSuites() as $suite) {
            array_map(array($event, 'addFeature'), $this->loadSuiteFeatures($suite, $locator));
        }
    }

    /**
     * Loads features for specific suite at specific locator.
     *
     * @param SuiteInterface $suite
     * @param null|string    $locator
     *
     * @return SuitedFeature[]
     *
     * @throws RuntimeException If loader for locator not found
     */
    private function loadSuiteFeatures(SuiteInterface $suite, $locator = null)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($suite, $locator)) {
                $suitedFeatures = array_map(
                    function ($feature) use ($suite) {
                        return new SuitedFeature($suite, $feature);
                    },
                    $loader->load($suite, $locator)
                );

                return $suitedFeatures;
            }
        }

        throw new RuntimeException(sprintf(
            'Can not find features loader for suite "%s" & locator "%s".',
            $suite->getName(),
            $locator
        ));
    }
}
