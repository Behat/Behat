<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

/**
 * Reads the `@api` / `@internal` tags that define our public API.
 *
 * PHPStan's reflection exposes `@internal` but not `@api`, so we read the raw docblocks ourselves and treat both
 * tags the same way for consistency.
 */
final class ApiTags
{
    /**
     * @param list<string> $apiNamespacePrefixes psr-4 roots of this package, from composer.json
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly array $apiNamespacePrefixes,
    ) {
    }

    /**
     * Is this a type of ours, and so one we expect to carry the tag?
     *
     * Anything else - PHP built-ins, Symfony, and behat/gherkin, which is a separate package with its own BC policy -
     * is never expected to be marked and is not our business here.
     */
    public function isOurs(string $class): bool
    {
        foreach ($this->apiNamespacePrefixes as $prefix) {
            if (str_starts_with($class, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function isClassMarkedApi(string $class): bool
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return false;
        }

        return $this->hasTag($this->reflectionProvider->getClass($class)->getNativeReflection()->getDocComment(), 'api');
    }

    public function isClassMarkedInternal(string $class): bool
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return false;
        }

        return $this->hasTag($this->reflectionProvider->getClass($class)->getNativeReflection()->getDocComment(), 'internal');
    }

    public function isApiClass(ClassReflection $class): bool
    {
        $doc = $class->getNativeReflection()->getDocComment();

        return !$this->hasTag($doc, 'internal') && $this->hasTag($doc, 'api');
    }

    /**
     * A member marked `@internal` is excluded from the promise even when its class is `@api`, and a member marked
     * `@api` is included even when its class is not.
     */
    public function isApiMethod(ClassReflection $class, string $method): bool
    {
        $native = $class->getNativeReflection();
        $doc = $native->hasMethod($method) ? $native->getMethod($method)->getDocComment() : false;

        if ($this->hasTag($doc, 'internal')) {
            return false;
        }

        return $this->hasTag($doc, 'api') || $this->isApiClass($class);
    }

    private function hasTag(string|false|null $docComment, string $tag): bool
    {
        return is_string($docComment)
            && preg_match('/^\s*\*\s*@' . $tag . '\b/m', $docComment) === 1;
    }
}
