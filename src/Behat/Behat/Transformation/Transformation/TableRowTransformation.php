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
 * Table row transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @phpstan-import-type TBehatCallable from RuntimeCallee
 */
final class TableRowTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^row\:[[:print:]]+$/u';

    public static function supportsPatternAndMethod(string $pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, (string) $pattern);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     *
     * @phpstan-param TBehatCallable $callable
     */
    public function __construct(
        private $pattern,
        callable|array $callable,
        ?string $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, int|string $argumentIndex, $argumentArgumentValue): bool
    {
        if (!$argumentArgumentValue instanceof TableNode) {
            return false;
        }

        return $this->pattern === 'row:' . implode(',', $argumentArgumentValue->getRow(0));
    }

    /**
     * @return list<mixed>
     */
    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, int|string $argumentIndex, $argumentValue): array
    {
        $rows = [];
        foreach ($argumentValue as $row) {
            $call = new TransformationCall(
                $definitionCall->getEnvironment(),
                $definitionCall->getCallee(),
                $this,
                [$row]
            );

            $result = $callCenter->makeCall($call);

            if ($result->hasException()) {
                throw $result->getException();
            }

            $rows[] = $result->getReturn();
        }

        return $rows;
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function __toString(): string
    {
        return 'TableRowTransform ' . $this->pattern;
    }
}
