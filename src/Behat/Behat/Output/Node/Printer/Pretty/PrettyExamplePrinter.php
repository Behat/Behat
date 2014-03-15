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
     * @var PrettyPathPrinter
     */
    private $pathPrinter;
    /**
     * @var string
     */
    private $indentText;

    /**
     * Initializes printer.
     *
     * @param PrettyPathPrinter $pathPrinter
     * @param integer           $indentation
     */
    public function __construct(PrettyPathPrinter $pathPrinter, $indentation = 6)
    {
        $this->pathPrinter = $pathPrinter;
        $this->indentText = str_repeat(' ', intval($indentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, ExampleNode $example)
    {
        $this->printTitle($formatter->getOutputPrinter(), $example);
        $this->pathPrinter->printScenarioPath($formatter, $feature, $example, mb_strlen($this->indentText, 'utf8'));
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
    }

    /**
     * Prints example title.
     *
     * @param OutputPrinter $printer
     * @param ExampleNode   $example
     */
    private function printTitle(OutputPrinter $printer, ExampleNode $example)
    {
        $printer->write(sprintf('%s%s', $this->indentText, $example->getTitle()));
    }
}
