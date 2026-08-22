<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

/**
 * Splits the title & description of a gherkin node into the parts we want to print.
 */
final class PrettyTitleAndDescriptionSplitter
{
    /**
     * @param string|null $title the node title, which in legacy parsing mode may also contain the description
     * @param string|null $description the node description, if the node type & parsing mode supports one
     *
     * @return array{title: ?string, description: ?string}
     */
    public function split(?string $title, ?string $description): array
    {
        if ($description !== null && $description !== '') {
            // The node was parsed in gherkin-32 mode, where the description is a separate property and the
            // title is guaranteed to be a single line.
            return [
                'title' => $title,
                'description' => $description,
            ];
        }

        // Could have been parsed in gherkin-32 mode with no description, or in legacy mode with a multi-line title
        // either way the title is the first line (if any) and the description is the rest.
        $lines = explode("\n", (string) $title);
        $firstLine = array_shift($lines);

        return [
            'title' => $firstLine,
            'description' => $lines === [] ? null : implode("\n", $lines),
        ];
    }
}
