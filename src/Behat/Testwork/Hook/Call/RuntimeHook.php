<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Call\RuntimeCallee;
use Behat\Testwork\Hook\Hook;

/**
 * Represents a hook executed during the execution runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeHook extends RuntimeCallee implements Hook
{
    /**
     * @var string
     */
    private $scopeName;

    /**
     * Initializes hook.
     *
     * @param string      $scopeName
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($scopeName, $callable, $description = null)
    {
        $this->scopeName = $scopeName;

        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeName()
    {
        return $this->scopeName;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }
}
