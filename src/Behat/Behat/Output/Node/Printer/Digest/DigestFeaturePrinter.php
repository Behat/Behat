<?php

namespace Behat\Behat\Output\Node\Printer\Digest;

use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

class DigestFeaturePrinter implements FeaturePrinter
{
    protected $indentText;

    public function __construct($indentation = 0, $subIndentation = 2)
    {
        $this->indentText = str_repeat(' ', intval($indentation));
    }

    public function printFooter(Formatter $formatter, TestResult $result)
    {
        $formatter->getOutputPrinter()->writeln();
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature)
    {
        $printer = $formatter->getOutputPrinter();
        $printer->write(sprintf('%s{+pending_param}%s{-pending_param}', $this->indentText, $feature->getKeyword()));

        if ($title = $feature->getTitle()) {
            $printer->write(sprintf(' {+passed}%s{-passed} {+comment}%s{-comment}', $title, $feature->getFile()));
        }

        $printer->writeln();
    }
}
