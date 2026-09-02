<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Step;

use Behat\Gherkin\Node\PyStringNode;
use Stringable;

/**
 * Represents a Gherkin doc string passed to a step definition.
 *
 * Use this type instead of {@see PyStringNode} in step definition parameters
 * to keep them decoupled from the Gherkin AST.
 *
 * @api
 */
final class DocString implements Stringable
{
    public function __construct(
        private readonly PyStringNode $pyStringNode,
    ) {
    }

    /**
     * Returns the text of the doc string.
     */
    public function getContent(): string
    {
        return $this->pyStringNode->getRaw();
    }

    public function __toString(): string
    {
        return $this->pyStringNode->getRaw();
    }
}
