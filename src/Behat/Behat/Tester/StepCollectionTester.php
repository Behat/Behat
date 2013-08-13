<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\SkipStepTesterCarrierEvent;
use Behat\Behat\Tester\Event\StepTesterCarrierEvent;
use Behat\Gherkin\Node\StepNode;
use RuntimeException;

/**
 * Step collections tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class StepCollectionTester extends DispatchingService
{
    /**
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     * @param integer              $result
     *
     * @return StepTester
     *
     * @throws RuntimeException If step tester is not found
     */
    protected function getStepTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step,
        $result
    )
    {
        if (StepEvent::PASSED < $result) {
            $testerProvider = new SkipStepTesterCarrierEvent($suite, $contexts, $step);
        } else {
            $testerProvider = new StepTesterCarrierEvent($suite, $contexts, $step);
        }

        $this->dispatch(EventInterface::CREATE_STEP_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for "%s" step of "%s" scenario in the "%s" suite.',
                $step->getText(),
                $step->getParent()->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
