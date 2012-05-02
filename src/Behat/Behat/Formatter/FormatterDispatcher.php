<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\Translation\Translator,
    Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Formatter dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatterDispatcher
{
    private $class;
    private $name;
    private $description;

    /**
     * Initializes formatter dispatcher.
     *
     * @param string $class       Formatter class
     * @param string $name        Name of the formatter
     * @param string $description Formatter description
     *
     * @throws \RuntimeException
     */
    public function __construct($class, $name = null, $description = null)
    {
        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface('Behat\Behat\Formatter\FormatterInterface')) {
            throw new \RuntimeException(sprintf(
                'Formatter class "%s" should implement FormatterInterface', $class
            ));
        }

        $this->class       = $class;
        $this->name        = null !== $name ? strtolower($name) : null;
        $this->description = $description;
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns formatter class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Initializes formatter instance.
     *
     * @return FormatterInterface
     */
    public function createFormatter()
    {
        $class = $this->class;

        return new $class();
    }
}
