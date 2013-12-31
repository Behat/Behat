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
use Behat\Testwork\Call\CallCentre;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Repository argument transformer.
 *
 * Argument transformer based on transformations repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RepositoryArgumentTransformer implements ArgumentTransformer
{
    /**
     * @var TransformationRepository
     */
    private $repository;
    /**
     * @var CallCentre
     */
    private $callCentre;
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
     * @param CallCentre               $callCentre
     * @param PatternTransformer       $patternTransformer
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        TransformationRepository $repository,
        CallCentre $callCentre,
        PatternTransformer $patternTransformer,
        TranslatorInterface $translator
    ) {
        $this->repository = $repository;
        $this->callCentre = $callCentre;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
    }

    /**
     * Checks if transformer supports argument.
     *
     * @param DefinitionCall $definitionCall
     * @param integer|string $argumentIndex
     * @param mixed          $argumentValue
     *
     * @return Boolean
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return true;
    }

    /**
     * Transforms argument value using transformation and returns a new one.
     *
     * @param DefinitionCall $definitionCall
     * @param integer|string $argumentIndex
     * @param mixed          $argumentValue
     *
     * @return mixed
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
    protected function transform(DefinitionCall $definitionCall, Transformation $transformation, $index, $value)
    {
        if (is_object($value) && !$value instanceof ArgumentInterface) {
            return $value;
        }

        $pattern = $transformation->getPattern();

        if ($this->isTokenAndMatchesPattern($index, $pattern)) {
            $newValue = $this->execute($definitionCall, $transformation, array($value));

            return $newValue ? : $value;
        }

        if ($this->isTableAndMatchesPattern($value, $pattern)) {
            $newValue = $this->execute($definitionCall, $transformation, array($value));

            return $newValue ? : $value;
        }

        if ($this->isStringAndMatchesPattern($definitionCall, $value, $pattern, $match)) {
            $newValue = $this->execute($definitionCall, $transformation, array_slice($match, 1));

            return $newValue ? : $value;
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

        $result = $this->callCentre->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    /**
     * Checks if argument is a token and matches pattern.
     *
     * @param $argumentIndex
     * @param $pattern
     *
     * @return Boolean
     */
    private function isTokenAndMatchesPattern($argumentIndex, $pattern)
    {
        return is_string($argumentIndex) && (':' . $argumentIndex === $pattern);
    }

    /**
     * Checks if argument is a table and matches pattern.
     *
     * @param $argumentValue
     * @param $pattern
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
    private function isStringAndMatchesPattern(
        DefinitionCall $definitionCall,
        $argumentValue,
        $pattern,
        &$match
    ) {
        $regex = $this->getRegex(
            $definitionCall->getEnvironment()->getSuiteName(),
            $pattern,
            $definitionCall->getFeature()->getLanguage()
        );

        return preg_match($regex, $argumentValue, $match);
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
            return $this->patternTransformer->toRegex($pattern);
        }

        return $this->patternTransformer->toRegex($translatedPattern);
    }
}
