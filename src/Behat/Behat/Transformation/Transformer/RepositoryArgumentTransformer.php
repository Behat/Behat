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

        if ($this->isApplicableTokenTransformation($transformation)) {
            return $this->applyTokenTransformation($definitionCall, $transformation, $index, $value);
        }

        if ($this->isApplicableTableTransformation($transformation, $value)) {
            return $this->applyTableTransformation($definitionCall, $transformation, $value);
        }

        if ($this->isApplicablePatternTransformation($definitionCall, $transformation, $value, $arguments)) {
            return $this->applyPatternTransformation($definitionCall, $transformation, $arguments);
        }

        return $value;
    }

    /**
     * Checks if provided transformation is token-based.
     *
     * @param Transformation $transformation
     *
     * @return Boolean
     */
    private function isApplicableTokenTransformation(Transformation $transformation)
    {
        return 1 === preg_match('/^\:\w+$/', $transformation->getPattern());
    }

    /**
     * Applies provided token transformation.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param integer|string $index
     * @param mixed          $value
     *
     * @return mixed
     */
    private function applyTokenTransformation(DefinitionCall $definitionCall, Transformation $transformation, $index, $value)
    {
        return $this->isArgumentIndexMatchesTokenPattern($index, $transformation->getPattern())
            ? $this->execute($definitionCall, $transformation, array($value))
            : $value;
    }

    /**
     * Checks if argument index matches token pattern.
     *
     * @param integer|string $index
     * @param string         $pattern
     *
     * @return Boolean
     */
    private function isArgumentIndexMatchesTokenPattern($index, $pattern)
    {
        return ':' . $index === $pattern;
    }

    /**
     * Checks if provided transformation is applicable table transformation.
     *
     * @param Transformation $transformation
     * @param mixed          $value
     *
     * @return Boolean
     */
    private function isApplicableTableTransformation(Transformation $transformation, $value)
    {
        if (!$value instanceof TableNode) {
            return false;
        };

        return $transformation->getPattern() === 'table:' . implode(',', $value->getRow(0));
    }

    /**
     * Applies provided table transformation.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param mixed          $value
     *
     * @return mixed
     */
    private function applyTableTransformation(DefinitionCall $definitionCall, Transformation $transformation, $value)
    {
        return $this->execute($definitionCall, $transformation, array($value));
    }

    /**
     * Checks if provided transformation is applicable pattern transformation.
     *
     * @param DefinitionCall        $definitionCall
     * @param Transformation|string $transformation
     * @param mixed                 $value
     * @param array                 $match
     *
     * @return Boolean
     */
    private function isApplicablePatternTransformation(DefinitionCall $definitionCall, Transformation $transformation, $value, &$match)
    {
        $regex = $this->getRegex(
            $definitionCall->getEnvironment()->getSuite()->getName(),
            $transformation->getPattern(),
            $definitionCall->getFeature()->getLanguage()
        );

        if (is_string($value) && preg_match($regex, $value, $match)) {
            // take arguments from capture groups if there are some
            if (count($match) > 1) {
                $match = array_slice($match, 1);
            }

            return true;
        }

        return false;
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

    /**
     * Applies provided pattern transformation.
     *
     * @param DefinitionCall $definitionCall
     * @param Transformation $transformation
     * @param array          $arguments
     *
     * @return mixed
     */
    private function applyPatternTransformation(DefinitionCall $definitionCall, Transformation $transformation, array $arguments)
    {
        return $this->execute($definitionCall, $transformation, $arguments);
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
}
