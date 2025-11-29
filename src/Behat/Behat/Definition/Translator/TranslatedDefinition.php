<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Translator;

use Behat\Behat\Definition\Definition;
use Stringable;

/**
 * Represents definition translated to the specific language.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TranslatedDefinition implements Stringable, Definition
{
    /**
     * Initialises translated definition.
     *
     * @param string     $translatedPattern
     * @param string     $language
     */
    public function __construct(
        private readonly Definition $definition,
        private $translatedPattern,
        private $language,
    ) {
    }

    public function getType()
    {
        return $this->definition->getType();
    }

    public function getPattern()
    {
        return $this->translatedPattern;
    }

    /**
     * Returns original (not translated) pattern.
     *
     * @return string
     */
    public function getOriginalPattern()
    {
        return $this->definition->getPattern();
    }

    /**
     * Returns language definition was translated to.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    public function getDescription()
    {
        return $this->definition->getDescription();
    }

    public function getPath()
    {
        return $this->definition->getPath();
    }

    public function isAMethod()
    {
        return $this->definition->isAMethod();
    }

    public function isAnInstanceMethod()
    {
        return $this->definition->isAnInstanceMethod();
    }

    public function getCallable()
    {
        return $this->definition->getCallable();
    }

    public function getReflection()
    {
        return $this->definition->getReflection();
    }

    public function getOriginalDefinition(): Definition
    {
        return $this->definition;
    }

    public function __toString()
    {
        return $this->definition->__toString();
    }
}
