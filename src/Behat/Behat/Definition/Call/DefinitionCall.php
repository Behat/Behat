<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

use Behat\Behat\Definition\Definition;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Environment\Environment;

/**
 * Enhances environment call with definition information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @method Definition getCallee()
 */
final class DefinitionCall extends EnvironmentCall
{
    /**
     * Initializes definition call.
     *
     * @param int|null $errorReportingLevel
     */
    public function __construct(
        Environment $environment,
        private readonly FeatureNode $feature,
        private readonly StepNode $step,
        Definition $definition,
        array $arguments,
        $errorReportingLevel = null,
    ) {
        parent::__construct($environment, $definition, $arguments, $errorReportingLevel);
    }

    /**
     * Returns step feature node.
     */
    public function getFeature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns definition step node.
     */
    public function getStep(): StepNode
    {
        return $this->step;
    }
}
