<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Call;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Environment\Environment;

/**
 * Call extended with transformation information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TransformationCall extends EnvironmentCall
{
    /**
     * Initializes call.
     */
    public function __construct(
        Environment $environment,
        private readonly Definition $definition,
        Transformation $transformation,
        array $arguments,
    ) {
        parent::__construct($environment, $transformation, $arguments);
    }

    /**
     * Returns transformed definition.
     */
    public function getDefinition(): Definition
    {
        return $this->definition;
    }
}
