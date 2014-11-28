<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Gherkin;

use Behat\Behat\Tester\Context\BackgroundContext;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Tests provided Gherkin background.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BackgroundTester implements Tester
{
    /**
     * @var Tester
     */
    private $containerTester;

    /**
     * Initializes tester.
     *
     * @param Tester $containerTester
     */
    public function __construct(Tester $containerTester)
    {
        $this->containerTester = $containerTester;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        $context = $this->castContext($context);

        return $this->containerTester->test($context, $control);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param Context $context
     *
     * @return BackgroundContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
    {
        if ($context instanceof BackgroundContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'BackgroundTester tests instances of BackgroundContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
