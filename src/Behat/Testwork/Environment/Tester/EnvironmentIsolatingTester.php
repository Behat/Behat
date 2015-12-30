<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Tester;

use Behat\Testwork\Environment\Context\EnvironmentIsolatingContext;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Tester;

/**
 * Isolates context environment before passing it to the decorated tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentIsolatingTester implements Tester
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
     * @return EnvironmentIsolatingContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
    {
        if ($context instanceof EnvironmentIsolatingContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'EnvironmentIsolatingTester tests instances of EnvironmentIsolatingContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
