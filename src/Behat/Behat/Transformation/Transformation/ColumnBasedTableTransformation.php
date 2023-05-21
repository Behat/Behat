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

/**
 * Column-based table transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ColumnBasedTableTransformation extends RuntimeCallee implements SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^table\:(?:\*|[[:print:]]+)$/';

    /**
     * @var string
     */
    private $pattern;

    /**
     * {@inheritdoc}
     */
    static public function supportsPatternAndMethod($pattern, ReflectionMethod $method)
    {
        return 1 === preg_match(self::PATTERN_REGEX, $pattern);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentArgumentValue)
    {
        if (!$argumentArgumentValue instanceof TableNode) {
            return false;
        };

        return $this->pattern === 'table:' . implode(',', $argumentArgumentValue->getRow(0))
            || $this->pattern === 'table:*';
    }

    /**
     * {@inheritdoc}
     */
    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            array($argumentValue)
        );

        $result = $callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 50;
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
        return 'ColumnTableTransform ' . $this->pattern;
    }
}
