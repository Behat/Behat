<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Hook\Scope\SuiteScope;

/**
 * Represents BeforeSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuite extends RuntimeSuiteHook
{
    /**
     * Initializes hook.
     *
     * @param string|null $filterString
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($filterString, $callable, $description = null)
    {
        parent::__construct(SuiteScope::BEFORE, $filterString, $callable, $description);
    }

    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName()
    {
        return 'BeforeSuite';
    }
}
