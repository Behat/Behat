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
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\FeatureTesterCarrierEvent;
use Behat\Gherkin\Node\FeatureNode;
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
     *
     * @return integer
     */
    public function test(SuiteInterface $suite, array $features)
    {
        $contexts = $this->createContextPool($suite);

        $event = new SuiteEvent($suite, $contexts);
        $this->dispatch(EventInterface::BEFORE_SUITE, $event);

        $result = 0;
        foreach ($features as $feature) {
            $tester = $this->getFeatureTester($suite, $contexts, $feature);
            $result = max($result, $tester->test($suite, $contexts, $feature));
        }

        $event = new SuiteEvent($suite, $contexts);
        $this->dispatch(EventInterface::AFTER_SUITE, $event);

        return $result;
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
        $testerProvider = new Event\FeatureTesterCarrierEvent($suite, $contexts, $feature);

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
