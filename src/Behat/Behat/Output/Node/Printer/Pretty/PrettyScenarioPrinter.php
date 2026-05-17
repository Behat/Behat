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
use Behat\Gherkin\Node\DescribableNodeInterface;
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
    private readonly string $subIndentText;
    private readonly PrettyDescriptionPrinter $descriptionPrinter;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(
        private readonly PrettyPathPrinter $pathPrinter,
        $indentation = 2,
        $subIndentation = 4,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
        $this->descriptionPrinter = new PrettyDescriptionPrinter();
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario): void
    {
        if ($scenario instanceof TaggedNodeInterface) {
            $this->printTags($formatter->getOutputPrinter(), $scenario->getTags());
        }

        ['title' => $title, 'description' => $description] = $this->getTitleAndDescription($scenario);

        $this->printKeyword($formatter->getOutputPrinter(), $scenario->getKeyword());
        $this->printTitle($formatter->getOutputPrinter(), $title ?? '');
        $this->pathPrinter->printScenarioPath($formatter, $feature, $scenario, mb_strlen($this->indentText, 'utf8'));
        $this->descriptionPrinter->printDescription($formatter->getOutputPrinter(), $description ?? '', $this->subIndentText);
    }

    /**
     * @return array{title: ?string, description: ?string}
     */
    private function getTitleAndDescription(Scenario $scenario): array
    {
        if ($scenario instanceof DescribableNodeInterface && $scenario->getDescription()) {
            // All ScenarioLikeInterface defined by behat/gherkin are also DescribableNodeInterface
            // but we can't guarantee that's true if the node has come from third-party code.
            //
            // If it does match this interface and was parsed in gherkin-32 mode the description
            // will be in the description property and the title is guaranteed to be a single line.
            return [
                'title' => $scenario->getTitle(),
                'description' => $scenario->getDescription(),
            ];
        }

        // Could have been parsed in gherkin-32 mode with no description, or in legacy mode with a multi-line title
        // either way the title is the first line (if any) and the description is the rest.
        $lines = explode("\n", (string) $scenario->getTitle());
        $title = array_shift($lines);

        return [
            'title' => $title,
            'description' => $lines === [] ? null : implode("\n", $lines),
        ];
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

        $tags = array_map($this->prependTagWithTagSign(...), $tags);
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
     * Prints scenario title.
     */
    private function printTitle(OutputPrinter $printer, string $title): void
    {
        if ('' !== $title) {
            $printer->write(sprintf(' %s', $title));
        }
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
