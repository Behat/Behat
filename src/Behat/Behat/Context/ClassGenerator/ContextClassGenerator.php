<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ClassGenerator;

use Behat\Behat\Context\Suite\Setup\ContextSetup;

/**
 * Context class generator interface.
 *
 * Used by ContextPool suite setup to generate context classes.
 *
 * @see ContextSetup
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextClassGenerator
{
    /**
     * Checks if generator supports provided context class.
     *
     * @param string $classname
     *
     * @return Boolean
     */
    public function supportsClassname($classname);

    /**
     * Generates context class code.
     *
     * @param string $classname
     *
     * @return string
     */
    public function generateClass($classname);
}
