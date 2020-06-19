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

/**
 * Row-based table transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RowBasedTableTransformation extends RuntimeCallee implements SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^rowtable\:[[:print:]]+$/';

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
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $value)
    {
        if (!$value instanceof TableNode) {
            return false;
        };

        // What we're doing here is checking that we have a 2 column table.
        // This bit checks we have two columns
        try {
            $value->getColumn(1);
        } catch (NodeException $e) {
            return false;
        }

        // And here we check we don't have a 3rd column
        try {
            $value->getColumn(2);
        } catch (NodeException $e) {
            // Once we know the table could be a row table, we check against the pattern.
            return $this->pattern === 'rowtable:' . implode(',', $value->getColumn(0));
        }

        return false;
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
        return 'RowTableTransform ' . $this->pattern;
    }
}
