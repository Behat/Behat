<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Call\Handler;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\Handler\CallHandler;
use Exception;

/**
 * Definition arguments transformer handler.
 *
 * Handles definition calls by intercepting them and transforming their arguments using transformations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionArgumentsTransformer implements CallHandler
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
     * Handles call and returns either new call, call result or a null.
     *
     * @param DefinitionCall $definitionCall
     *
     * @return CallResult|DefinitionCall
     */
    public function handleCall(Call $definitionCall)
    {
        $newArguments = array();
        $transformed = false;
        foreach ($definitionCall->getArguments() as $index => $value) {
            $newValue = $value;

            foreach ($this->argumentTransformers as $transformer) {
                if (!$transformer->supportsDefinitionAndArgument($definitionCall, $index, $newValue)) {
                    continue;
                }

                try {
                    $newValue = $transformer->transformArgument($definitionCall, $index, $newValue);
                } catch (Exception $e) {
                    return new CallResult($definitionCall, null, $e, null);
                }

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
