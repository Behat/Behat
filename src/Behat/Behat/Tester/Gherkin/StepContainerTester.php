<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Gherkin;

use Behat\Behat\Tester\Context\StepContainerContext;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Tests all steps in provided Gherkin step container.
 *
 * This tester is used to test Gherkin backgrounds, scenarios and examples.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepContainerTester implements Tester
{
    /**
     * @var Tester
     */
    private $stepTester;

    /**
     * Initializes tester.
     *
     * @param Tester $stepTester
     */
    public function __construct(Tester $stepTester)
    {
        $this->stepTester = $stepTester;
    }

    /**
     * {@inheritdoc}
     *
     * This method might introduce side-effects into the run control.
     * If step tests fail, the further tests skipping will be enforced.
     */
    public function test(Context $context, RunControl $control)
    {
        $context = $this->castContext($context);
        $results = array();

        foreach ($context->getSteps() as $step) {
            $stepContext = $context->createStepContext($step);
            $stepResult = $this->stepTester->test($stepContext, $control);
            $results[] = $stepResult;
            $control = $stepResult->isPassed() ? $control : RunControl::skip();
        }

        return new TestResults($results);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param Context $context
     *
     * @return StepContainerContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
    {
        if ($context instanceof StepContainerContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'StepContainerTester tests instances of StepContainerContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
