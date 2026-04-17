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
 *
 * @api
 */
final class HookStat
{
    private ?HookScope $scope = null;

    /**
     * Initializes hook stat.
     */
    public function __construct(
        private readonly string $name,
        private readonly string $path,
        private readonly ?string $error = null,
        private readonly ?string $stdOut = null,
    ) {
    }

    public function setScope(HookScope $scope): void
    {
        $this->scope = $scope;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSuccessful(): bool
    {
        return null === $this->error;
    }

    /**
     * Returns hook standard output (if has some).
     */
    public function getStdOut(): ?string
    {
        return $this->stdOut;
    }

    /**
     * Returns hook exception.
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Returns hook path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function getScope(): HookScope
    {
        return $this->scope;
    }
}
