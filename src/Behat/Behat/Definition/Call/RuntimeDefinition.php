<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Call\RuntimeCallee;

/**
 * Runtime definition.
 *
 * Definition created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeDefinition extends RuntimeCallee implements Definition
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $pattern;

    /**
     * Initializes definition.
     *
     * @param string      $type
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($type, $pattern, $callable, $description = null)
    {
        $this->type = $type;
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns step pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Represents definition as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getType() . ' ' . $this->getPattern();
    }
}
