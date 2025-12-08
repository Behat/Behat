<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

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
final class PrettyScenarioPrinter implements ScenarioPrinter
{
    /**
     * @var string
     */
    private $indentText;
    /**
     * @var string
     */
    private $subIndentText;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(
        private readonly PrettyPathPrinter $pathPrinter,
        $indentation = 2,
        $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario): void
    {
        if ($scenario instanceof TaggedNodeInterface) {
            $this->printTags($formatter->getOutputPrinter(), $scenario->getTags());
        }

        $this->printKeyword($formatter->getOutputPrinter(), $scenario->getKeyword());
        $this->printTitle($formatter->getOutputPrinter(), $scenario->getTitle());
        $this->pathPrinter->printScenarioPath($formatter, $feature, $scenario, mb_strlen($this->indentText, 'utf8'));
        $this->printDescription($formatter->getOutputPrinter(), $scenario->getTitle());
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints scenario tags.
     *
     * @param string[]      $tags
     */
    private function printTags(OutputPrinter $printer, array $tags): void
    {
        if (!count($tags)) {
            return;
        }

        $tags = array_map([$this, 'prependTagWithTagSign'], $tags);
        $printer->writeln(sprintf('%s{+tag}%s{-tag}', $this->indentText, implode(' ', $tags)));
    }

    /**
     * Prints scenario keyword.
     *
     * @param string        $keyword
     */
    private function printKeyword(OutputPrinter $printer, $keyword): void
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $keyword));
    }

    /**
     * Prints scenario title (first line of long title).
     *
     * @param string|null   $longTitle
     */
    private function printTitle(OutputPrinter $printer, $longTitle): void
    {
        $description = explode("\n", $longTitle ?? '');
        $title = array_shift($description);

        if ('' !== $title) {
            $printer->write(sprintf(' %s', $title));
        }
    }

    /**
     * Prints scenario description (other lines of long title).
     *
     * @param string|null   $longTitle
     */
    private function printDescription(OutputPrinter $printer, $longTitle): void
    {
        $lines = explode("\n", $longTitle ?? '');
        array_shift($lines);

        foreach ($lines as $line) {
            $printer->writeln(sprintf('%s%s', $this->subIndentText, $line));
        }
    }

    /**
     * Prepends tags string with tag-sign.
     *
     * @param string $tag
     */
    private function prependTagWithTagSign($tag): string
    {
        if (str_starts_with($tag, '@')) {
            return $tag;
        }

        // The legacy mode of the behat/gherkin parser is trimming the `@` from tags so we need to re-add it for pretty-printing
        return '@' . $tag;
    }
}
