<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\BackgroundTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatching background tester.
 *
 * Background tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DispatchingBackgroundTester extends BackgroundTester
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Sets event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function testBackground(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $this->eventDispatcher and $this->eventDispatcher->dispatch(
            BackgroundTested::BEFORE,
            new BackgroundTested($suite, $environment, $feature, $feature->getBackground())
        );

        $results = parent::testBackground($suite, $environment, $feature, $skip);

        $this->eventDispatcher and $this->eventDispatcher->dispatch(
            BackgroundTested::AFTER,
            new BackgroundTested($suite, $environment, $feature, $feature->getBackground(), $results)
        );

        return $results;
    }
}
