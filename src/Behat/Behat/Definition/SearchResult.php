<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

/**
 * Step definition search result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SearchResult
{
    /**
     * Registers search match.
     *
     * @param string|null $matchedText
     */
    public function __construct(
        private readonly ?Definition $definition = null,
        private $matchedText = null,
        private readonly ?array $arguments = null,
    ) {
    }

    /**
     * Checks if result contains a match.
     */
    public function hasMatch(): bool
    {
        return $this->definition instanceof Definition;
    }

    /**
     * Returns matched definition.
     */
    public function getMatchedDefinition(): ?Definition
    {
        return $this->definition;
    }

    /**
     * Returns matched text.
     *
     * @return string|null
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Returns matched definition arguments.
     */
    public function getMatchedArguments(): ?array
    {
        return $this->arguments;
    }
}
