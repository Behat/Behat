<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Scope;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Transformation\Transformation;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Throwable;

/**
 * Represents the step definition call whose arguments are being transformed.
 *
 * Transformations receive this rather than the definition call itself, so that they can
 * inspect the call being made and run their own callable without depending on internals
 * of Behat's call pipeline.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface TransformationScope
{
    /**
     * Runs the given transformation with the provided arguments and returns its return value.
     *
     * The transformation is invoked through the same call pipeline as any other Behat callable,
     * so error handling and output capturing apply as usual. Any exception thrown by the
     * transformation itself is rethrown to the caller.
     *
     * @param mixed[] $arguments
     *
     * @throws Throwable if the transformation throws
     */
    public function call(Transformation $transformation, array $arguments): mixed;

    /**
     * Returns the environment that the step is being run in.
     */
    public function getEnvironment(): Environment;

    /**
     * Returns the feature that the step belongs to.
     */
    public function getFeature(): FeatureNode;

    /**
     * Returns the step being called.
     */
    public function getStep(): StepNode;

    /**
     * Returns the step definition that matched the step, whose arguments are being transformed.
     */
    public function getDefinition(): Definition;
}
