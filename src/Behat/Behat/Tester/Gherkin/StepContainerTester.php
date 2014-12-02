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
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Exercise\BasicRunControl;
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
     */
    public function test(TestContext $context, RunControl $control)
    {
        $results = array();
        $context = $this->castContext($context);

        foreach ($context->getSteps() as $step) {
            $stepContext = $context->createStepContext($step);
            $results[] = $result = $this->stepTester->test($stepContext, $control);
            $control = $result->isPassed() ? $control : BasicRunControl::skipAll();
        }

        return new TestResults($results);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param TestContext $context
     *
     * @return StepContainerContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
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
