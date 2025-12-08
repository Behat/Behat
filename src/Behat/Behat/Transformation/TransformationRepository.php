<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;

/**
 * Provides transformations using environment manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TransformationRepository
{
    /**
     * Initializes repository.
     */
    public function __construct(
        private readonly EnvironmentManager $environmentManager,
    ) {
    }

    /**
     * Returns all available definitions for a specific environment.
     *
     * @return Transformation[]
     */
    public function getEnvironmentTransformations(Environment $environment)
    {
        return array_filter(
            $this->environmentManager->readEnvironmentCallees($environment),
            fn (Callee $callee): bool => $callee instanceof Transformation
        );
    }
}
