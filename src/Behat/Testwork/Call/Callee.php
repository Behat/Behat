<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use ReflectionFunctionAbstract;

/**
 * Represents callable object.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 *
 * @phpstan-type TBehatCallable callable|array{class-string, string}
 */
interface Callee
{
    /**
     * Returns callee definition path.
     */
    public function getPath(): string;

    /**
     * Returns callee description.
     */
    public function getDescription(): ?string;

    /**
     * Returns true if callee is a method, false otherwise.
     */
    public function isAMethod(): bool;

    /**
     * Returns true if callee is an instance (non-static) method, false otherwise.
     */
    public function isAnInstanceMethod(): bool;

    /**
     * Returns callable.
     *
     * @phpstan-return TBehatCallable
     */
    public function getCallable();

    /**
     * Returns callable reflection.
     */
    public function getReflection(): ReflectionFunctionAbstract;
}
