<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Scope;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\Definition;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Behat\Transformation\Transformation;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Environment\Environment;

/**
 * Exposes a definition call to transformations, and runs them against Behat's call center.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeTransformationScope implements TransformationScope
{
    /**
     * @param DefinitionCall $definitionCall the call whose arguments are being transformed
     */
    public function __construct(
        private readonly DefinitionCall $definitionCall,
        private readonly CallCenter $callCenter,
    ) {
    }

    public function call(Transformation $transformation, array $arguments): mixed
    {
        $result = $this->callCenter->makeCall(new TransformationCall(
            $this->definitionCall->getEnvironment(),
            $this->definitionCall->getCallee(),
            $transformation,
            $arguments
        ));

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    public function getEnvironment(): Environment
    {
        return $this->definitionCall->getEnvironment();
    }

    public function getFeature(): FeatureNode
    {
        return $this->definitionCall->getFeature();
    }

    public function getStep(): StepNode
    {
        return $this->definitionCall->getStep();
    }

    public function getDefinition(): Definition
    {
        return $this->definitionCall->getCallee();
    }
}
