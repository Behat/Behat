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

/**
 * Represents runtime hook, filterable by filter string.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeFilterableHook extends RuntimeHook implements FilterableHook
{
    /**
     * @var string|null
     */
    private $filterString;

    /**
     * Initializes hook.
     *
     * @param string      $scopeName
     * @param string|null $filterString
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($scopeName, $filterString, $callable, $description = null)
    {
        $this->filterString = $filterString;

        parent::__construct($scopeName, $callable, $description);
    }

    /**
     * Returns hook filter string (if has one).
     *
     * @return string|null
     */
    public function getFilterString()
    {
        return $this->filterString;
    }

    public function __toString()
    {
        return trim($this->getName() . ' ' . $this->getFilterString());
    }
}
