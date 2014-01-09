<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ClassGenerator;

use Behat\Behat\Context\Suite\Setup\SuiteWithContextsSetup;
use Behat\Testwork\Suite\Suite;

/**
 * Context class generator interface.
 *
 * Used by context suite setup to generate context classes.
 *
 * @see SuiteWithContextsSetup
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextClassGenerator
{
    /**
     * Checks if generator supports provided context class.
     *
     * @param Suite  $suite
     * @param string $classname
     *
     * @return Boolean
     */
    public function supportsSuiteAndClassname(Suite $suite, $classname);

    /**
     * Generates context class code.
     *
     * @param Suite  $suite
     * @param string $classname
     *
     * @return string The context class source code
     */
    public function generateClass(Suite $suite, $classname);
}
