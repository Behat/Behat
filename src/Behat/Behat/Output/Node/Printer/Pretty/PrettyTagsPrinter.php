<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Testwork\Output\Printer\OutputPrinter;

final class PrettyTagsPrinter
{
    /**
     * @param string[] $tags
     */
    public function printTags(OutputPrinter $printer, array $tags, string $indentText): void
    {
        if (!count($tags)) {
            return;
        }

        $tags = array_map($this->prependTagWithTagSign(...), $tags);
        $printer->writeln(sprintf('%s{+tag}%s{-tag}', $indentText, implode(' ', $tags)));
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
