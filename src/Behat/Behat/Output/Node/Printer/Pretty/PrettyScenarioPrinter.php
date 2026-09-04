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
    private readonly string $indentText;
    private readonly string $subIndentText;
    private readonly PrettyDescriptionPrinter $descriptionPrinter;
    private readonly PrettyTagsPrinter $tagsPrinter;
    private readonly PrettyTitleAndDescriptionSplitter $titleAndDescriptionSplitter;

    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly PrettyPathPrinter $pathPrinter,
        int $indentation = 2,
        int $subIndentation = 4,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
        $this->descriptionPrinter = new PrettyDescriptionPrinter();
        $this->tagsPrinter = new PrettyTagsPrinter();
        $this->titleAndDescriptionSplitter = new PrettyTitleAndDescriptionSplitter();
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario): void
    {
        if ($scenario instanceof TaggedNodeInterface) {
            $this->tagsPrinter->printTags($formatter->getOutputPrinter(), $scenario->getTags(), $this->indentText);
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
        // All ScenarioLikeInterface defined by behat/gherkin are also DescribableNodeInterface
        // but we can't guarantee that's true if the node has come from third-party code.
        $description = $scenario instanceof DescribableNodeInterface ? $scenario->getDescription() : null;

        return $this->titleAndDescriptionSplitter->split($scenario->getTitle(), $description);
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints scenario keyword.
     */
    private function printKeyword(OutputPrinter $printer, string $keyword): void
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
}
