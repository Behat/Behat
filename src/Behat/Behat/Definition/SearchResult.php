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
     * @var null|Definition
     */
    private $definition;
    /**
     * @var null|string
     */
    private $matchedText;
    /**
     * @var null|array
     */
    private $arguments;

    /**
     * Registers search match.
     *
     * @param null|Definition $definition
     * @param null|string     $matchedText
     * @param null|array      $arguments
     */
    public function __construct(Definition $definition = null, $matchedText = null, array $arguments = null)
    {
        $this->definition = $definition;
        $this->matchedText = $matchedText;
        $this->arguments = $arguments;
    }

    /**
     * Checks if result contains a match.
     *
     * @return Boolean
     */
    public function hasMatch()
    {
        return null !== $this->definition;
    }

    /**
     * Returns matched definition.
     *
     * @return null|Definition
     */
    public function getMatchedDefinition()
    {
        return $this->definition;
    }

    /**
     * Returns matched text.
     *
     * @return null|string
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Returns matched definition arguments.
     *
     * @return null|array
     */
    public function getMatchedArguments()
    {
        return $this->arguments;
    }
}
