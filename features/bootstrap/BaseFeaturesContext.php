<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat test suite base context.
 * Used to demonstrate inheritance abilities of contexts.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BaseFeaturesContext extends BehatContext
{
    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param   string       $filename   name of the file (relative path)
     * @param   PyStringNode $content    PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content) {}

    /**
     * Moves user to the specified path.
     *
     * @Given /^I am in the "([^"]*)" path$/
     *
     * @param   string  $path
     */
    public function iAmInThePath($path) {}
}
