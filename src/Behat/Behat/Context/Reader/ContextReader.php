<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;

/**
 * Context reader interface.
 *
 * Reads callees from a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextReader
{
    /**
     * Reads callees from specific environment & context.
     *
     * @param Environment $environment
     * @param string      $contextClassname
     *
     * @return Callee[]
     */
    public function readContextCallees(Environment $environment, $contextClassname);
}
