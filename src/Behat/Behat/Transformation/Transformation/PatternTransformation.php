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
use UnexpectedValueException;

/**
 * Pattern-based transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PatternTransformation extends RuntimeCallee implements Stringable, Transformation
{
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

    /**
     * Checks if transformer supports argument.
     */
    public function supportsDefinitionAndArgument(
        RegexGenerator $regexGenerator,
        DefinitionCall $definitionCall,
        mixed $argumentValue,
    ): bool {
        $regex = $regexGenerator->generateRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $this->pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        return $this->match($regex, $argumentValue) !== false;
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
        mixed $argumentValue,
    ): mixed {
        $regex = $regexGenerator->generateRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $this->pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        $arguments = $this->match($regex, $argumentValue);

        if ($arguments === false) {
            // This should never happen, as supportsDefinitionAndArgument() should have been called first and would have
            // returned false if the argument does not match the regex.
            throw new UnexpectedValueException(
                __METHOD__.' was called with an unsupported argument - was supportsDefinitionAndArgument() called first?'
            );
        }

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

    public function __toString(): string
    {
        return 'PatternTransform ' . $this->pattern;
    }

    /**
     * @return list<string>|false
     */
    private function match(string $regexPattern, mixed $argumentValue): array|false
    {
        if (is_string($argumentValue) && preg_match($regexPattern, $argumentValue, $match)) {
            // take arguments from capture groups if there are some
            if (count($match) > 1) {
                $match = array_slice($match, 1);
            }

            return $match;
        }

        return false;
    }
}
