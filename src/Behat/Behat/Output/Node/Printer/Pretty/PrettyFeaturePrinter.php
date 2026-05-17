<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints feature header and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyFeaturePrinter implements FeaturePrinter
{
    /**
     * @var string
     */
    private $indentText;
    private readonly string $subIndentText;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct($indentation = 0, $subIndentation = 2)
    {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature): void
    {
        $this->printTags($formatter->getOutputPrinter(), $feature->getTags());

        $this->printTitle($formatter->getOutputPrinter(), $feature);
        $this->printDescription($formatter->getOutputPrinter(), $feature);
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
    }

    /**
     * Prints feature tags.
     *
     * @param string[]      $tags
     */
    private function printTags(OutputPrinter $printer, array $tags): void
    {
        if (!count($tags)) {
            return;
        }

        $tags = array_map($this->prependTagWithTagSign(...), $tags);
        $printer->writeln(sprintf('%s{+tag}%s{-tag}', $this->indentText, implode(' ', $tags)));
    }

    /**
     * Prints feature title using provided printer.
     */
    private function printTitle(OutputPrinter $printer, FeatureNode $feature): void
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $feature->getKeyword()));

        if ($title = $feature->getTitle()) {
            $printer->write(sprintf(' %s', $title));
        }

        $printer->writeln();
    }

    /**
     * Prints feature description using provided printer.
     */
    private function printDescription(OutputPrinter $printer, FeatureNode $feature): void
    {
        if (!$feature->getDescription()) {
            $printer->writeln();

            return;
        }

        // Leading whitespace is handled differently in different parsing modes:
        // - In gherkin-32, nothing is trimmed and the text exactly matches the feature file.
        // - In legacy, the parser removes {keywordIndent + 2} spaces from the start of every line.
        //
        // For consistent output between modes, we need to find the indentation of the first line (if any). Then
        // un-indent every line by that amount, then re-indent by our desired indentation.
        //
        // The trade-off is that the output might not match the exact indentation within the source feature file.
        $lines = explode("\n", $feature->getDescription());
        $internalIndent = preg_match('/^\s*/', $lines[0], $matches) ? $matches[0] : '';

        foreach (explode("\n", $feature->getDescription()) as $descriptionLine) {
            $descriptionLine = preg_replace('/^'.$internalIndent.'/', '', $descriptionLine);
            $printer->writeln(sprintf('%s%s', $this->subIndentText, $descriptionLine));
        }

        $printer->writeln();
    }

    /**
     * Prepends tags string with tag-sign.
     */
    private function prependTagWithTagSign(string $tag): string
    {
        if (str_starts_with($tag, '@')) {
            return $tag;
        }

        // The legacy mode of the behat/gherkin parser is trimming the `@` from tags so we need to re-add it for pretty-printing
        return '@' . $tag;
    }
}
