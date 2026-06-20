<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Hook\FilterableHook;
use Stringable;

/**
 * Represents runtime hook, filterable by filter string.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeFilterableHook extends RuntimeHook implements Stringable, FilterableHook
{
    /**
     * Initializes hook.
     */
    public function __construct(
        string $scopeName,
        private readonly ?string $filterString,
        callable $callable,
        ?string $description = null,
    ) {
        parent::__construct($scopeName, $callable, $description);
    }

    /**
     * Returns hook filter string (if has one).
     */
    public function getFilterString(): ?string
    {
        return $this->filterString;
    }

    public function __toString(): string
    {
        return trim($this->getName() . ' ' . $this->getFilterString());
    }
}
