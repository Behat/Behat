<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Call\Filter;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Exception\UnsupportedCallException;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\CallFilter;

/**
 * Handles definition calls by intercepting them and transforming their arguments using transformations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionArgumentsTransformer implements CallFilter
{
    /**
     * @var ArgumentTransformer[]
     */
    private $argumentTransformers = array();

    /**
     * Registers new argument transformer.
     *
     * @param ArgumentTransformer $transformer
     */
    public function registerArgumentTransformer(ArgumentTransformer $transformer)
    {
        $this->argumentTransformers[] = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCall(Call $call)
    {
        return $call instanceof DefinitionCall;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCall(Call $call)
    {
        if (!$call instanceof DefinitionCall) {
            throw new UnsupportedCallException(sprintf(
                'DefinitionArgumentTransformer can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        $newArguments = array();
        $transformed = false;
        foreach ($call->getArguments() as $index => $value) {
            $newValue = $this->transformArgument($call, $index, $value);

            if ($newValue !== $value) {
                $transformed = true;
            }

            $newArguments[$index] = $newValue;
        }

        if (!$transformed) {
            return $call;
        }

        return new DefinitionCall(
            $call->getEnvironment(),
            $call->getFeature(),
            $call->getStep(),
            $call->getCallee(),
            $newArguments,
            $call->getErrorReportingLevel()
        );
    }

    /**
     * Transforms call argument using registered transformers.
     *
     * @param DefinitionCall $definitionCall
     * @param integer|string $index
     * @param mixed          $value
     *
     * @return mixed
     */
    private function transformArgument(DefinitionCall $definitionCall, $index, $value)
    {
        foreach ($this->argumentTransformers as $transformer) {
            if (!$transformer->supportsDefinitionAndArgument($definitionCall, $index, $value)) {
                continue;
            }

            return $transformer->transformArgument($definitionCall, $index, $value);
        }

        return $value;
    }
}
