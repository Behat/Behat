<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformation;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Behat\Transformation\SimpleArgumentTransformation;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\RuntimeCallee;
use ReflectionMethod;
use Stringable;

/**
 * Column-based table transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ColumnBasedTableTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^table\:(?:\*|[[:print:]]+)$/u';

    public static function supportsPatternAndMethod(string $pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, (string) $pattern);
    }

    /**
     * Initializes transformation.
     */
    public function __construct(
        private readonly string $pattern,
        callable $callable,
        ?string $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, int|string $argumentIndex, mixed $argumentArgumentValue): bool
    {
        if (!$argumentArgumentValue instanceof TableNode) {
            return false;
        }

        return $this->pattern === 'table:' . implode(',', $argumentArgumentValue->getRow(0))
            || $this->pattern === 'table:*';
    }

    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, int|string $argumentIndex, mixed $argumentValue): mixed
    {
        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            [$argumentValue]
        );

        $result = $callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function __toString(): string
    {
        return 'ColumnTableTransform ' . $this->pattern;
    }
}
