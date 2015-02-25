<?php

namespace Behat\Behat\Output\Node\Printer\Digest;

use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Behat\Output\Node\Printer\SetupPrinter;

class DigestSetupPrinter implements SetupPrinter
{
    /**
     * {@inheritdoc}
     */
    public function printSetup(Formatter $formatter, Setup $setup)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function printTeardown(Formatter $formatter, Teardown $teardown)
    {
    }
}

