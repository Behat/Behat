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
     * @var Definition|null
     */
    private $definition;
    /**
     * @var string|null
     */
    private $matchedText;
    /**
     * @var array|null
     */
    private $arguments;

    /**
     * Registers search match.
     *
     * @param string|null $matchedText
     */
    public function __construct(?Definition $definition = null, $matchedText = null, ?array $arguments = null)
    {
        $this->definition = $definition;
        $this->matchedText = $matchedText;
        $this->arguments = $arguments;
    }

    /**
     * Checks if result contains a match.
     *
     * @return bool
     */
    public function hasMatch()
    {
        return null !== $this->definition;
    }

    /**
     * Returns matched definition.
     *
     * @return Definition|null
     */
    public function getMatchedDefinition()
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
     *
     * @return array|null
     */
    public function getMatchedArguments()
    {
        return $this->arguments;
    }
}
