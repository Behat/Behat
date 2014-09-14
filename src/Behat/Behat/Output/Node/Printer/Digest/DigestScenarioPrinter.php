<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Digest;

use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\TaggedNodeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints scenario headers (with tags, keyword and long title) and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DigestScenarioPrinter implements ScenarioPrinter
{
    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario)
    {
        $printer = $formatter->getOutputPrinter();


        $title = $scenario->getTitle();
        if ('' === $title) {
            $title = '{+exception}Untitled scenario{-exception}';
        }
        $printer->write(sprintf(' {+comment}%s{-comment} %s', $scenario->getLine(), $title));
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
        $formatter->getOutputPrinter()->writeln();
    }
}
