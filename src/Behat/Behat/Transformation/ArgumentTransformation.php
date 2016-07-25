<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Testwork\Call\CallCenter;

/**
 * Represents a single transformation capable of changing single argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArgumentTransformation extends Transformation
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
     * @param CallCenter     $callCenter
     * @param DefinitionCall $definitionCall
     * @param integer|string $argumentIndex
     * @param mixed          $argumentValue
     *
     * @return TransformationCall
     */
    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, $argumentIndex, $argumentValue);
}
