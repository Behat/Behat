<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents hook stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookStat
{
    /**
     * @var HookScope|null
     */
    private $scope;

    /**
     * Initializes hook stat.
     *
     * @param string      $name
     * @param string      $path
     * @param string|null $error
     * @param string|null $stdOut
     */
    public function __construct(
        private $name,
        private $path,
        private $error = null,
        private $stdOut = null,
    ) {
    }

    public function setScope(HookScope $scope): void
    {
        $this->scope = $scope;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isSuccessful(): bool
    {
        return null === $this->error;
    }

    /**
     * Returns hook standard output (if has some).
     *
     * @return string|null
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Returns hook exception.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns hook path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getScope(): HookScope
    {
        return $this->scope;
    }
}
