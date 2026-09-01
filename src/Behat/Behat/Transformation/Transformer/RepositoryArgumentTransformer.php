<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Behat\Transformation\RegexGenerator;
use Behat\Behat\Transformation\Scope\TransformationScope;
use Behat\Behat\Transformation\SimpleArgumentTransformation;
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\Transformation\PatternTransformation;
use Behat\Behat\Transformation\TransformationRepository;
use Behat\Gherkin\Node\ArgumentInterface;

/**
 * Argument transformer based on transformations repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RepositoryArgumentTransformer implements ArgumentTransformer, RegexGenerator
{
    /**
     * Initializes transformer.
     */
    public function __construct(
        private readonly TransformationRepository $repository,
        private readonly PatternTransformer $patternTransformer,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function supportsDefinitionAndArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): bool
    {
        return count($this->repository->getEnvironmentTransformations($scope->getEnvironment())) > 0;
    }

    public function transformArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): mixed
    {
        $environment = $scope->getEnvironment();
        [$simpleTransformations, $normalTransformations] = $this->splitSimpleAndNormalTransformations(
            $this->repository->getEnvironmentTransformations($environment)
        );

        $newValue = $this->applySimpleTransformations($simpleTransformations, $scope, $argumentIndex, $argumentValue);
        $newValue = $this->applyNormalTransformations($normalTransformations, $scope, $argumentIndex, $newValue);

        return $newValue;
    }

    public function generateRegex(string $suiteName, string $pattern, string $language): string
    {
        $translatedPattern = $this->translator->trans($pattern, [], $suiteName, $language);
        if ($pattern === $translatedPattern) {
            return $this->patternTransformer->transformPatternToRegex($pattern);
        }

        return $this->patternTransformer->transformPatternToRegex($translatedPattern);
    }

    /**
     * Apply simple argument transformations in priority order.
     *
     * @param SimpleArgumentTransformation[] $transformations
     */
    private function applySimpleTransformations(array $transformations, TransformationScope $scope, int|string $index, mixed $value): mixed
    {
        usort($transformations, fn (SimpleArgumentTransformation $t1, SimpleArgumentTransformation $t2): int => $t2->getPriority() <=> $t1->getPriority());

        $newValue = $value;
        foreach ($transformations as $transformation) {
            $newValue = $this->transform($scope, $transformation, $index, $newValue);
        }

        return $newValue;
    }

    /**
     * Apply normal (non-simple) argument transformations.
     *
     * @param Transformation[] $transformations
     */
    private function applyNormalTransformations(array $transformations, TransformationScope $scope, int|string $index, mixed $value): mixed
    {
        $newValue = $value;
        foreach ($transformations as $transformation) {
            $newValue = $this->transform($scope, $transformation, $index, $newValue);
        }

        return $newValue;
    }

    /**
     * Transforms argument value using registered transformers.
     */
    private function transform(TransformationScope $scope, Transformation $transformation, int|string $index, mixed $value): mixed
    {
        if (is_object($value) && !$value instanceof ArgumentInterface) {
            return $value;
        }

        if ($transformation instanceof SimpleArgumentTransformation
            && $transformation->supportsDefinitionAndArgument($scope, $index, $value)) {
            return $transformation->transformArgument($scope, $index, $value);
        }

        if ($transformation instanceof PatternTransformation
            && $transformation->supportsDefinitionAndArgument($this, $scope, $value)) {
            return $transformation->transformArgument($this, $scope, $value);
        }

        return $value;
    }

    /**
     * Splits transformations into simple and normal ones.
     *
     * @param Transformation[] $transformations
     *
     * @return array{list<SimpleArgumentTransformation>, list<Transformation>}
     */
    private function splitSimpleAndNormalTransformations(array $transformations): array
    {
        return array_reduce($transformations, fn ($acc, $t): array => [
            $t instanceof SimpleArgumentTransformation ? array_merge($acc[0], [$t]) : $acc[0],
            $t instanceof SimpleArgumentTransformation ? $acc[1] : array_merge($acc[1], [$t]),
        ], [[], []]);
    }
}
