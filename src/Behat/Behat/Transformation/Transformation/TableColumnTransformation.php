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

final class TableColumnTransformation extends RuntimeCallee implements SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^column\:[[:print:]]+$/u';

    private string $pattern;

    static public function supportsPatternAndMethod($pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }

    public function __construct(string $pattern, callable|array $callable, ?string $description = null)
    {
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(
        DefinitionCall $definitionCall,
        $argumentIndex,
        $argumentArgumentValue
    ): bool {
        if (!$argumentArgumentValue instanceof TableNode && !is_array($argumentArgumentValue)) {
            return false;
        };

        if (!str_starts_with($this->pattern, 'column:')) {
            return false;
        }
        $columnNames = explode(',', substr($this->pattern, 7));

        if ($argumentArgumentValue instanceof TableNode) {
            $tableHeadings = $argumentArgumentValue->getRow(0);
            foreach ($columnNames as $columnName) {
                if (in_array($columnName, $tableHeadings, true)) {
                    return true;
                }
                return false;
            }
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
        $argumentValue
    ): array
    {
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
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'TableColumnTransform ' . $this->pattern;
    }
}
