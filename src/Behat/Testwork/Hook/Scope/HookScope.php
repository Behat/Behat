<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\HookRepository;
use Behat\Testwork\Suite\Suite;

/**
 * Represents an object used to find appropriate hooks.
 *
 * @see HookDispatcher
 * @see HookRepository
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface HookScope
{
    /**
     * Returns hook scope name.
     */
    public function getName(): string;

    /**
     * Returns hook suite.
     */
    public function getSuite(): Suite;

    /**
     * Returns hook environment.
     */
    public function getEnvironment(): Environment;
}
