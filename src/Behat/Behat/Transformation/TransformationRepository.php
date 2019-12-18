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
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes repository.
     *
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->environmentManager = $environmentManager;
    }

    /**
     * Returns all available definitions for a specific environment.
     *
     * @param Environment $environment
     *
     * @return Transformation[]
     */
    public function getEnvironmentTransformations(Environment $environment)
    {
        return array_filter(
            $this->environmentManager->readEnvironmentCallees($environment),
            function (Callee $callee) {
                return $callee instanceof Transformation;
            }
        );
    }
}
