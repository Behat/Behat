<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\ExamplePrinter;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints example header (usually simply an example row) and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyExamplePrinter implements ExamplePrinter
{
    /**
     * @var string
     */
    private $indentText;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     */
    public function __construct(
        private readonly PrettyPathPrinter $pathPrinter,
        $indentation = 6,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, ExampleNode $example): void
    {
        $this->printTitle($formatter->getOutputPrinter(), $example);
        $this->pathPrinter->printScenarioPath($formatter, $feature, $example, mb_strlen($this->indentText, 'utf8'));
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
    }

    /**
     * Prints example title.
     */
    private function printTitle(OutputPrinter $printer, ExampleNode $example): void
    {
        $printer->write(sprintf('%s%s', $this->indentText, $example->getTitle()));
    }
}
