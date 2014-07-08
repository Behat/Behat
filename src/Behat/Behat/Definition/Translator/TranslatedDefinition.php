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

/**
 * Represents definition translated to the specific language.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TranslatedDefinition implements Definition
{
    /**
     * @var Definition
     */
    private $definition;
    /**
     * @var string
     */
    private $translatedPattern;
    /**
     * @var string
     */
    private $language;

    /**
     * Initialises translated definition.
     *
     * @param Definition $definition
     * @param string     $translatedPattern
     * @param string     $language
     */
    public function __construct(Definition $definition, $translatedPattern, $language)
    {
        $this->definition = $definition;
        $this->translatedPattern = $translatedPattern;
        $this->language = $language;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->definition->getType();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->definition->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->definition->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function isAMethod()
    {
        return $this->definition->isAMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function isAnInstanceMethod()
    {
        return $this->definition->isAnInstanceMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable()
    {
        return $this->definition->getCallable();
    }

    /**
     * {@inheritdoc}
     */
    public function getReflection()
    {
        return $this->definition->getReflection();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->definition->__toString();
    }
}
