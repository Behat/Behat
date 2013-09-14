<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Event\ContextPoolCarrierEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\FeatureTesterCarrierEvent;
use Behat\Gherkin\Node\FeatureNode;
use Exception;
use RuntimeException;

/**
 * Suite tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTester extends DispatchingService
{
    /**
     * Tests provided suite features.
     *
     * @param SuiteInterface $suite
     * @param FeatureNode[]  $features
     * @param Boolean        $skip
     *
     * @return integer
     */
    public function test(SuiteInterface $suite, array $features, $skip = false)
    {
        $status = $skip ? StepEvent::SKIPPED : StepEvent::PASSED;

        $contexts = $this->createContextPool($suite);

        $event = new SuiteEvent($suite, $contexts);
        $this->dispatch(EventInterface::BEFORE_SUITE, $event);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_BEFORE_SUITE, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $skip = true;
        }

        foreach ($features as $feature) {
            $tester = $this->getFeatureTester($suite, $contexts, $feature);
            $status = max($status, $tester->test($suite, $contexts, $feature, $skip));
        }

        $event = new SuiteEvent($suite, $contexts, $status);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_AFTER_SUITE, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $event = new SuiteEvent($suite, $contexts, $status);
        }

        $this->dispatch(EventInterface::AFTER_SUITE, $event);

        return $status;
    }

    /**
     * Returns context pool instance.
     *
     * @param SuiteInterface $suite
     *
     * @return ContextPoolInterface
     *
     * @throws RuntimeException If context pool could not be created
     */
    private function createContextPool(SuiteInterface $suite)
    {
        $contextPoolProvider = new ContextPoolCarrierEvent($suite);

        $this->dispatch(EventInterface::CREATE_CONTEXT_POOL, $contextPoolProvider);
        if (!$contextPoolProvider->hasContextPool()) {
            throw new RuntimeException(sprintf(
                'Can not create context pool for "%s" suite. Is this suite configured properly?',
                $suite->getName()
            ));
        }

        return $contextPoolProvider->getContextPool();
    }

    /**
     * Returns feature tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param FeatureNode          $feature
     *
     * @return FeatureTester
     *
     * @throws RuntimeException If feature tester is not found
     */
    private function getFeatureTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        FeatureNode $feature
    )
    {
        $testerProvider = new FeatureTesterCarrierEvent($suite, $contexts, $feature);

        $this->dispatch(EventInterface::CREATE_FEATURE_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for "%s" feature in the "%s" suite.',
                $feature->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
