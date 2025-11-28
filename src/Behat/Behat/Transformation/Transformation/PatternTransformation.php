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
use Behat\Behat\Transformation\RegexGenerator;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\RuntimeCallee;
use Exception;
use Stringable;

/**
 * Pattern-based transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PatternTransformation extends RuntimeCallee implements Stringable, Transformation
{
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct(
        private $pattern,
        $callable, $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    /**
     * Checks if transformer supports argument.
     *
     * @return bool
     */
    public function supportsDefinitionAndArgument(
        RegexGenerator $regexGenerator,
        DefinitionCall $definitionCall,
        $argumentValue,
    ) {
        $regex = $regexGenerator->generateRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $this->pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        return $this->match($regex, $argumentValue, $match);
    }

    /**
     * Transforms argument value using transformation and returns a new one.
     *
     * @throws Exception If transformation throws exception
     */
    public function transformArgument(
        RegexGenerator $regexGenerator,
        CallCenter $callCenter,
        DefinitionCall $definitionCall,
        $argumentValue,
    ) {
        $regex = $regexGenerator->generateRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $this->pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        $this->match($regex, $argumentValue, $arguments);

        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            $arguments
        );

        $result = $callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
    {
        return 'PatternTransform ' . $this->pattern;
    }

    /**
     * @return bool
     */
    private function match($regexPattern, $argumentValue, &$match)
    {
        if (is_string($argumentValue) && preg_match($regexPattern, $argumentValue, $match)) {
            // take arguments from capture groups if there are some
            if (count($match) > 1) {
                $match = array_slice($match, 1);
            }

            return true;
        }

        return false;
    }
}
