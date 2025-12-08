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
use Behat\Gherkin\Exception\NodeException;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\RuntimeCallee;
use ReflectionMethod;
use Stringable;

/**
 * Row-based table transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RowBasedTableTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^rowtable\:[[:print:]]+$/u';

    public static function supportsPatternAndMethod($pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct(
        private $pattern,
        $callable,
        $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentArgumentValue)
    {
        if (!$argumentArgumentValue instanceof TableNode) {
            return false;
        }

        // What we're doing here is checking that we have a 2 column table.
        // This bit checks we have two columns
        try {
            $argumentArgumentValue->getColumn(1);
        } catch (NodeException) {
            return false;
        }

        // And here we check we don't have a 3rd column
        try {
            $argumentArgumentValue->getColumn(2);
        } catch (NodeException) {
            // Once we know the table could be a row table, we check against the pattern.
            return $this->pattern === 'rowtable:' . implode(',', $argumentArgumentValue->getColumn(0));
        }

        return false;
    }

    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
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

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
    {
        return 'RowTableTransform ' . $this->pattern;
    }
}
