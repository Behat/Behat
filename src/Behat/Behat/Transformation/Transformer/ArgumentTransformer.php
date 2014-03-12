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

/**
 * Transforms a single argument value.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArgumentTransformer
{
    /**
     * Checks if transformer supports argument.
     *
     * @param DefinitionCall $definitionCall
     * @param integer|string $argumentIndex
     * @param mixed          $argumentValue
     *
     * @return Boolean
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue);

    /**
     * Transforms argument value using transformation and returns a new one.
     *
     * @param DefinitionCall $definitionCall
     * @param integer|string $argumentIndex
     * @param mixed          $argumentValue
     *
     * @return mixed
     */
    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue);
}
