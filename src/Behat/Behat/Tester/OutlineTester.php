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
use Behat\Behat\Event\OutlineEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ExampleTesterCarrierEvent;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use RuntimeException;

/**
 * Outline tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester extends DispatchingService
{
    /**
     * Tests outline.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param Boolean              $skip
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $skip = false
    )
    {
        $status = $skip ? StepEvent::SKIPPED : StepEvent::PASSED;

        $event = new OutlineEvent($suite, $contexts, $outline);
        $this->dispatch(EventInterface::BEFORE_OUTLINE, $event);

        foreach ($outline->getExamples() as $example) {
            $tester = $this->getExampleTester($suite, $contexts, $example);
            $status = max($status, $tester->test($suite, $contexts, $example, $skip));
        }

        $event = new OutlineEvent($suite, $contexts, $outline, $status);
        $this->dispatch(EventInterface::AFTER_OUTLINE, $event);

        return $status;
    }

    /**
     * Returns outline example tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ExampleNode          $example
     *
     * @return ExampleTester
     *
     * @throws RuntimeException If outline example tester is not found
     */
    private function getExampleTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ExampleNode $example
    )
    {
        $testerProvider = new ExampleTesterCarrierEvent($suite, $contexts, $example);

        $this->dispatch(EventInterface::CREATE_EXAMPLE_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for example #%d of "%s" outline from the "%s" suite.',
                $example->getLine(),
                $example->getOutline()->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
