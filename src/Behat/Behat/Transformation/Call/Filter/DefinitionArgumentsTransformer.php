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
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\CallFilter;

/**
 * Definition arguments transformer handler.
 *
 * Handles definition calls by intercepting them and transforming their arguments using transformations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionArgumentsTransformer implements CallFilter
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
     * Checks if handler supports call.
     *
     * @param Call $call
     *
     * @return Boolean
     */
    public function supportsCall(Call $call)
    {
        return $call instanceof DefinitionCall;
    }

    /**
     * Filters call and a new one.
     *
     * @param DefinitionCall $definitionCall
     *
     * @return DefinitionCall
     */
    public function filterCall(Call $definitionCall)
    {
        $newArguments = array();
        $transformed = false;
        foreach ($definitionCall->getArguments() as $index => $value) {
            $newValue = $value;

            foreach ($this->argumentTransformers as $transformer) {
                if (!$transformer->supportsDefinitionAndArgument($definitionCall, $index, $newValue)) {
                    continue;
                }

                $newValue = $transformer->transformArgument($definitionCall, $index, $newValue);

                break;
            }

            if ($newValue !== $value) {
                $transformed = true;
            }

            $newArguments[$index] = $newValue;
        }

        if (!$transformed) {
            return $definitionCall;
        }

        return new DefinitionCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getFeature(),
            $definitionCall->getStep(),
            $definitionCall->getCallee(),
            $newArguments,
            $definitionCall->getErrorReportingLevel()
        );
    }
}
