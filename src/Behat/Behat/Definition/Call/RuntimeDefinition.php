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
use Stringable;

/**
 * Represents a step definition created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeDefinition extends RuntimeCallee implements Stringable, Definition
{
    private bool $used = false;

    /**
     * Initializes definition.
     *
     * @param string      $type
     * @param string      $pattern
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct(
        private $type,
        private $pattern,
        $callable,
        $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
    {
        return $this->getType() . ' ' . $this->getPattern();
    }

    /**
     * @internal
     */
    public function markAsUsed(): void
    {
        $this->used = true;
    }

    /**
     * @internal
     */
    public function hasBeenUsed(): bool
    {
        return $this->used;
    }
}
