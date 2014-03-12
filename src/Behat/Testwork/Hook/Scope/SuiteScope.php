<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Represents a suite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SuiteScope extends HookScope
{
    const BEFORE = 'suite.before';
    const AFTER = 'suite.after';

    /**
     * Returns specification iterator.
     *
     * @return SpecificationIterator
     */
    public function getSpecificationIterator();
}
