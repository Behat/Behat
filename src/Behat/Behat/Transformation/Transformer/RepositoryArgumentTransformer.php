<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\TransformationRepository;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallCenter;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Argument transformer based on transformations repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RepositoryArgumentTransformer implements ArgumentTransformer
{
    /**
     * @var TransformationRepository
     */
    private $repository;
    /**
     * @var CallCenter
     */
    private $callCenter;
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes transformer.
     *
     * @param TransformationRepository $repository
     * @param CallCenter               $callCenter
     * @param PatternTransformer       $patternTransformer
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        TransformationRepository $repository,
        CallCenter $callCenter,
        PatternTransformer $patternTransformer,
        TranslatorInterface $translator
    ) {
        $this->repository = $repository;
        $this->callCenter = $callCenter;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $environment = $definitionCall->getEnvironment();
        $transformations = $this->repository->getEnvironmentTransformations($environment);

        $newValue = $argumentValue;
        foreach ($transformations as $transformation) {
            $newValue = $this->transform($definitionCall, $transformation, $argumentIndex, $newValue);
        }

        return $newValue;
    }

    /**
     * Transforms argument value using registered transformers.
     *
     * @param Transformation $transformation
     * @param DefinitionCall $definitionCall
     * @param integer|string $index
     * @param mixed          $value
     *
     * @return mixed
     */
    private function transform(DefinitionCall $definitionCall, Transformation $transformation, $index, $value)
    {
        if (is_object($value) && !$value instanceof ArgumentInterface) {
            return $value;
        }

        if (null !== $newValue = $this->transformToken($definitionCall, $transformation, $index, $value)) {
            return $newValue;
        }

        if (null !== $newValue = $this->transformTable($definitionCall, $transformation, $value)) {
            return $newValue;
        }

        if (null !== $newValue = $this->transformRegex($definitionCall, $transformation, $value)) {
            return $newValue;
        }

        return $value;
    }

    /**
     * Executes transformation.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param array          $arguments
     *
     * @return mixed
     *
     * @throws Exception If transformation call throws one
     */
    private function execute(DefinitionCall $definitionCall, Transformation $transformation, array $arguments)
    {
        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $transformation,
            $arguments
        );

        $result = $this->callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    /**
     * Transforms token argument.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param integer|string $index
     * @param mixed          $value
     *
     * @return null|mixed
     */
    private function transformToken(DefinitionCall $definitionCall, Transformation $transformation, $index, $value)
    {
        if (!$this->isTokenTransformation($transformation)) {
            return null;
        }

        if ($this->isIndexMatchesTokenPattern($index, $transformation->getPattern())) {
            return $this->execute($definitionCall, $transformation, array($value));
        }

        return $value;
    }

    /**
     * Transforms table argument.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param mixed          $value
     *
     * @return null|mixed
     */
    private function transformTable(DefinitionCall $definitionCall, Transformation $transformation, $value)
    {
        if ($this->isTableAndMatchesPattern($value, $transformation->getPattern())) {
            return $this->execute($definitionCall, $transformation, array($value));
        }

        return null;
    }

    /**
     * Transforms regex argument.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param mixed          $value
     *
     * @return null|mixed
     */
    private function transformRegex(DefinitionCall $definitionCall, Transformation $transformation, $value)
    {
        if ($this->isStringAndMatchesPattern($definitionCall, $value, $transformation->getPattern(), $match)) {
            // take arguments from capture groups if there are some
            if (count($match) > 1) {
                $match = array_slice($match, 1);
            }

            return $this->execute($definitionCall, $transformation, $match);
        }

        return null;
    }

    /**
     * Checks if provided transformation is token-based.
     *
     * @param Transformation $transformation
     *
     * @return Boolean
     */
    private function isTokenTransformation(Transformation $transformation)
    {
        return 1 === preg_match('/^\:\w+$/', $transformation->getPattern());
    }

    /**
     * Checks if argument index matches token pattern.
     *
     * @param integer|string $argumentIndex
     * @param string         $pattern
     *
     * @return Boolean
     */
    private function isIndexMatchesTokenPattern($argumentIndex, $pattern)
    {
        return ':' . $argumentIndex === $pattern;
    }

    /**
     * Checks if argument is a table and matches pattern.
     *
     * @param mixed  $argumentValue
     * @param string $pattern
     *
     * @return Boolean
     */
    private function isTableAndMatchesPattern($argumentValue, $pattern)
    {
        if (!$argumentValue instanceof TableNode) {
            return false;
        };

        return $pattern === 'table:' . implode(',', $argumentValue->getRow(0));
    }

    /**
     * Checks if argument is a string and matches pattern.
     *
     * @param DefinitionCall $definitionCall
     * @param mixed          $argumentValue
     * @param string         $pattern
     * @param array          $match
     *
     * @return Boolean
     */
    private function isStringAndMatchesPattern(DefinitionCall $definitionCall, $argumentValue, $pattern, &$match)
    {
        $regex = $this->getRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        return is_string($argumentValue) && preg_match($regex, $argumentValue, $match);
    }

    /**
     * Returns transformation regex.
     *
     * @param string $assetsId
     * @param string $pattern
     * @param string $language
     *
     * @return string
     */
    private function getRegex($assetsId, $pattern, $language)
    {
        $translatedPattern = $this->translator->trans($pattern, array(), $assetsId, $language);
        if ($pattern == $translatedPattern) {
            return $this->patternTransformer->transformPatternToRegex($pattern);
        }

        return $this->patternTransformer->transformPatternToRegex($translatedPattern);
    }
}
