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
use ReflectionFunctionAbstract;
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

    public function getType(): string
    {
        return $this->definition->getType();
    }

    public function getPattern(): string
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

    public function getDescription(): ?string
    {
        return $this->definition->getDescription();
    }

    public function getPath(): string
    {
        return $this->definition->getPath();
    }

    public function isAMethod(): bool
    {
        return $this->definition->isAMethod();
    }

    public function isAnInstanceMethod(): bool
    {
        return $this->definition->isAnInstanceMethod();
    }

    public function getCallable()
    {
        return $this->definition->getCallable();
    }

    public function getReflection(): ReflectionFunctionAbstract
    {
        return $this->definition->getReflection();
    }

    public function getOriginalDefinition(): Definition
    {
        return $this->definition;
    }

    public function __toString(): string
    {
        return $this->definition->__toString();
    }
}
