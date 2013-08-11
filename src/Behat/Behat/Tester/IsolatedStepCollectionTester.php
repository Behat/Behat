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
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\BackgroundTesterCarrierEvent;
use Behat\Gherkin\Node\BackgroundNode;
use RuntimeException;

/**
 * Class IsolatedStepCollectionTester
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class IsolatedStepCollectionTester extends StepCollectionTester
{
    /**
     * Initializes context pool.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     *
     * @return ContextPoolInterface
     */
    protected function initializeContextPool(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $contextPoolProvider = new ContextPoolCarrierEvent($suite, $contexts);
        $this->dispatch(EventInterface::INITIALIZE_CONTEXT_POOL, $contextPoolProvider);

        return $contextPoolProvider->getContextPool();
    }

    /**
     * Returns background tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param BackgroundNode       $background
     *
     * @throws RuntimeException If background tester is not found
     *
     * @return BackgroundTester
     */
    protected function getBackgroundTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        BackgroundNode $background
    )
    {
        $testerProvider = new Event\BackgroundTesterCarrierEvent($suite, $contexts, $background);

        $this->dispatch(EventInterface::CREATE_BACKGROUND_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for "%s" feature background from the "%s" suite.',
                $background->getFeature()->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
