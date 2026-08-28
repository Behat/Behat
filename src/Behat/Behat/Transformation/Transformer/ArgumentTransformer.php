<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Transformation\Scope\TransformationScope;

/**
 * Transforms a single argument value.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface ArgumentTransformer
{
    /**
     * Checks if transformer supports argument.
     */
    public function supportsDefinitionAndArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): bool;

    /**
     * Transforms argument value using transformation and returns a new one.
     */
    public function transformArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): mixed;
}
