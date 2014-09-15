<?php

namespace Behat\Behat\Output\Node\Printer\Digest;

use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

class DigestFeaturePrinter implements FeaturePrinter
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var integer
     */
    protected $indentText;

    public function __construct($basePath, $indentation = 0)
    {
        $this->basePath = $basePath;
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

        $printer->write(sprintf(
            ' {+passed}%s{-passed} {+comment}%s{-comment}',
            $feature->getTitle(),
            $this->relativizePaths($feature->getFile())
        ));


        $printer->writeln();
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    private function relativizePaths($path)
    {
        if (!$this->basePath) {
            return $path;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
