<?php

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Testwork\Output\Printer\OutputPrinter;

final class PrettyDescriptionPrinter
{
    public function printDescription(OutputPrinter $printer, string $description, string $subIndentText): void
    {
        if ('' === $description) {
            return;
        }

        // Leading whitespace is handled differently in different parsing modes:
        // - In gherkin-32, nothing is trimmed and the text exactly matches the feature file.
        // - In legacy, the parser removes {keywordIndent + 2} spaces from the start of every line.
        //
        // For consistent output between modes, we need to:
        // * Find the indentation of the first line (if any).
        // * Then un-indent every line by up to that amount.
        // * Then re-indent by our desired indentation.
        //
        // The trade-off is that the output might not match the exact indentation within the source feature file if:
        // - the block is indented differently to the expected {2 spaces more than the keyword / title line}.
        // - there are lines that are indented *less* than the first line of the block.
        //
        // I think these are acceptable trade-offs for the sake of consistent output (our past behaviour didn't
        // guarantee that it would match the feature file either).
        $lines = explode("\n", $description);
        $internalIndent = strlen(preg_match('/^( +)/', $lines[0], $matches) ? $matches[0] : '');

        foreach (explode("\n", $description) as $descriptionLine) {
            $descriptionLine = preg_replace('/^ {0,'.$internalIndent.'}/', '', $descriptionLine);
            $printer->writeln(sprintf('%s%s', $subIndentText, $descriptionLine));
        }
    }
}
