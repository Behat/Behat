<?php

use Behat\Behat\Context\AnnotatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class BaseFeaturesContext extends BehatContext implements AnnotatedContextInterface
{
    /**
     * @Given /^a file named "([^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $content) {}

    /**
     * @Given /^I am in the "([^"]*)" path$/
     */
    public function iAmInThePath($path) {}
}
