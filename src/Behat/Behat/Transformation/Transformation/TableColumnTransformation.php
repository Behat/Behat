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

final class TableColumnTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^column\:[[:print:]]+$/u';

    public static function supportsPatternAndMethod($pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }

    public function __construct(
        private readonly string $pattern,
        callable|array $callable,
        ?string $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(
        DefinitionCall $definitionCall,
        $argumentIndex,
        $argumentArgumentValue,
    ): bool {
        // The argument passed initially will be a TableNode but if a column transformation
        // has already been applied then this will have been transformed into an array already,
        // so we need to accept both possibilities
        if (!$argumentArgumentValue instanceof TableNode && !is_array($argumentArgumentValue)) {
            return false;
        }

        if (!str_starts_with($this->pattern, 'column:')) {
            return false;
        }
        $columnNames = explode(',', substr($this->pattern, 7));

        if ($argumentArgumentValue instanceof TableNode) {
            $tableHeadings = $argumentArgumentValue->getRow(0);

            return array_intersect($columnNames, $tableHeadings) !== [];
        }
        foreach ($argumentArgumentValue as $row) {
            $rowHasColumn = false;
            foreach ($columnNames as $columnName) {
                if (isset($row[$columnName])) {
                    $rowHasColumn = true;
                }
            }
            if (!$rowHasColumn) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param TableNode|array $argumentValue
     */
    public function transformArgument(
        CallCenter $callCenter,
        DefinitionCall $definitionCall,
        $argumentIndex,
        $argumentValue,
    ): array {
        $columnNames = explode(',', substr($this->pattern, 7));
        $rows = [];
        foreach ($argumentValue as $row) {
            foreach ($columnNames as $columnName) {
                if (isset($row[$columnName])) {
                    $call = new TransformationCall(
                        $definitionCall->getEnvironment(),
                        $definitionCall->getCallee(),
                        $this,
                        [$row[$columnName]]
                    );

                    $result = $callCenter->makeCall($call);

                    if ($result->hasException()) {
                        throw $result->getException();
                    }

                    $row[$columnName] = $result->getReturn();
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * The priority of this transformer needs to be less that the priority of the other table transformers because
     * we want to be able to transform whole tables or whole rows before we attempt to transform any column.
     */
    public function getPriority()
    {
        return 30;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
    {
        return 'TableColumnTransform ' . $this->pattern;
    }
}
