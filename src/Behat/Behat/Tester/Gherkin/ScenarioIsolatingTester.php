<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Gherkin;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Tester;

/**
 * Isolates scenario environment before passing it to the decorated tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioIsolatingTester implements Tester
{
    /**
     * @var Tester
     */
    private $decoratedTester;
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param Tester             $decoratedTester
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(Tester $decoratedTester, EnvironmentManager $environmentManager)
    {
        $this->decoratedTester = $decoratedTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function test(TestContext $context, RunControl $control)
    {
        $context = $this->castContext($context);
        $context = $context->createIsolatedContext($this->environmentManager);

        return $this->decoratedTester->test($context, $control);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param TestContext $context
     *
     * @return ScenarioContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
    {
        if ($context instanceof ScenarioContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'ScenarioTester tests instances of ScenarioContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
