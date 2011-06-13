<?php

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class BaseFeaturesContext extends BehatContext
{
    /**
     * @Given /^a file named "([^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $content) {}

    /**
     * @When /^I run "behat ([^"]*)"$/
     */
    public function iRunBehat($command) {}
}
