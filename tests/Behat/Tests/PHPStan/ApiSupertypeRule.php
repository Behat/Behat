<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionClass;
use ReflectionException;

/**
 * Keeps the supertypes of an `@api` type inside the public API.
 *
 * A type is reachable through its own ancestry, not only through method signatures: callers of an `@api` interface
 * may type against, catch, or implement anything it extends. `ContextException` is `@api` and extends
 * `TestworkException`, so the promise leaks unless that parent is covered too.
 *
 * Supertypes that are themselves marked `@internal` are left alone - see the note in processNode().
 *
 * @implements Rule<InClassNode>
 *
 * @see ApiBoundaryRule for the same contract applied to method signatures
 * @see https://github.com/Behat/Behat/issues/1237
 */
final class ApiSupertypeRule implements Rule
{
    public function __construct(
        private readonly ApiTags $apiTags,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if (!$this->apiTags->isApiClass($class)) {
            return [];
        }

        $errors = [];
        foreach ($this->supertypes($class->getName()) as $supertype) {
            if (!$this->apiTags->isOurs($supertype) || $this->apiTags->isClassMarkedApi($supertype)) {
                continue;
            }

            // A supertype marked `@internal` is a decision that has already been taken, not an oversight. Inheriting
            // one gives callers nothing they can reach: a marker interface behind a final attribute is never caught,
            // never handed to them and never implemented by them. Only undecided supertypes are worth reporting.
            if ($this->apiTags->isClassMarkedInternal($supertype)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s is part of the public API but %s, which it extends, is not marked @api.',
                $class->getName(),
                $supertype,
            ))
                ->identifier('behat.apiSupertype')
                ->tip(sprintf('Mark %s as @api, so that everything reachable through this type is covered by the promise.', $supertype))
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function supertypes(string $class): array
    {
        $native = $this->apiTagsReflection($class);

        if ($native === null) {
            return [];
        }

        $supertypes = $native->getInterfaceNames();

        for ($parent = $native->getParentClass(); $parent !== false; $parent = $parent->getParentClass()) {
            $supertypes[] = $parent->getName();
        }

        return array_values(array_unique($supertypes));
    }

    private function apiTagsReflection(string $class): ?ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (ReflectionException) {
            return null;
        }
    }
}
