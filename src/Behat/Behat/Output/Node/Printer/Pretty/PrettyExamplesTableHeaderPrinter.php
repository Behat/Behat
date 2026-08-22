<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints the tags, keyword, title, description and column headers of a single examples table.
 *
 * An outline may have any number of examples tables, so this is printed once before the first example of each of
 * them, preceded by a blank line to separate it from whatever came before.
 */
final class PrettyExamplesTableHeaderPrinter
{
    private readonly PrettyDescriptionPrinter $descriptionPrinter;
    private readonly PrettyTagsPrinter $tagsPrinter;
    private readonly PrettyTitleAndDescriptionSplitter $titleAndDescriptionSplitter;

    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
    ) {
        $this->descriptionPrinter = new PrettyDescriptionPrinter();
        $this->tagsPrinter = new PrettyTagsPrinter();
        $this->titleAndDescriptionSplitter = new PrettyTitleAndDescriptionSplitter();
    }

    public function printHeader(
        OutputPrinter $printer,
        ExampleTableNode $table,
        string $indentText,
        string $subIndentText,
    ): void {
        $printer->writeln();

        ['title' => $title, 'description' => $description] = $this->titleAndDescriptionSplitter->split(
            $table->getName(),
            $table->getDescription(),
        );

        $this->tagsPrinter->printTags($printer, $table->getTags(), $indentText);
        $this->printKeywordAndTitle($printer, $table->getKeyword(), $title ?? '', $indentText);
        $this->descriptionPrinter->printDescription($printer, $description ?? '', $subIndentText);
        $this->printColumnHeaders($printer, $table, $subIndentText);
    }

    private function printKeywordAndTitle(OutputPrinter $printer, string $keyword, string $title, string $indentText): void
    {
        $printer->writeln(sprintf(
            '%s{+keyword}%s:{-keyword}%s',
            $indentText,
            $keyword,
            '' !== $title ? ' ' . $title : '',
        ));
    }

    private function printColumnHeaders(OutputPrinter $printer, ExampleTableNode $table, string $subIndentText): void
    {
        $style = $this->resultConverter->convertResultCodeToString(TestResult::SKIPPED);
        $wrapper = fn ($col): string => sprintf('{+%s_param}%s{-%s_param}', $style, $col, $style);

        $printer->writeln(sprintf('%s%s', $subIndentText, $table->getRowAsStringWithWrappedValues(0, $wrapper)));
    }
}
