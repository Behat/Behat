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
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Keeps the public API self-contained.
 *
 * From 4.0 we only promise backwards compatibility for code marked `@api`. That promise is only meaningful if it is
 * closed over itself: a method callers are told they may use must not hand them, or demand of them, a type that is
 * outside the promise.
 *
 * This rule reports any public method of an `@api` class whose signature references a Behat type that is not itself
 * `@api`. Because marking a type `@api` subjects its own methods to the same check, the transitive closure is
 * enforced by induction - the rule never has to compute reachability itself.
 *
 * A member marked `@internal` is excluded from the promise even when its declaring class is `@api`, so it is not
 * checked. Types outside Behat's own psr-4 roots (PHP built-ins, Symfony, behat/gherkin - a separate package with
 * its own BC policy) are never expected to carry our tag and are ignored.
 *
 * @see ApiSupertypeRule for the same contract applied to a type's ancestry
 *
 * @implements Rule<InClassMethodNode>
 *
 * @see https://github.com/Behat/Behat/issues/1237
 */
final class ApiBoundaryRule implements Rule
{
    public function __construct(
        private readonly ApiTags $apiTags,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        $method = $node->getMethodReflection();

        // Only report against the class that declares the method, so an inherited method is not reported repeatedly.
        if ($method->getDeclaringClass()->getName() !== $class->getName()) {
            return [];
        }

        if (!$this->apiTags->isApiMethod($class, $method->getName()) || !$method->isPublic()) {
            return [];
        }

        $errors = [];
        foreach ($this->referencedTypes($method) as $referenced) {
            if (!$this->apiTags->isOurs($referenced) || $this->apiTags->isClassMarkedApi($referenced)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Method %s::%s() is part of the public API but references %s, which is not marked @api.',
                $class->getName(),
                $method->getName(),
                $referenced,
            ))
                ->identifier('behat.apiBoundary')
                ->tip(sprintf('Mark %s as @api, or keep it out of this signature. If this member is not really part of the public API, mark it @internal.', $referenced))
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function referencedTypes(ExtendedMethodReflection $method): array
    {
        // Behat has no overloaded signatures, so the single variant is the whole signature.
        $variant = $method->getVariants()[0];

        $types = [$variant->getReturnType()];
        foreach ($variant->getParameters() as $parameter) {
            $types[] = $parameter->getType();
        }

        $referenced = [];
        foreach ($types as $type) {
            // getReferencedClasses() unwraps unions, nullables, arrays-of and generic type arguments for us.
            foreach ($type->getReferencedClasses() as $class) {
                $referenced[$class] = true;
            }
        }

        return array_keys($referenced);
    }
}
