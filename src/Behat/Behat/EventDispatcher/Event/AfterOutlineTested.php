<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event after outline was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterOutlineTested extends OutlineTested implements AfterTested
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly OutlineNode $outline,
        private readonly TestResult $result,
        private readonly Teardown $teardown,
    ) {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function getFeature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns outline node.
     */
    public function getOutline(): OutlineNode
    {
        return $this->outline;
    }

    /**
     * Returns current test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     */
    public function getTeardown(): Teardown
    {
        return $this->teardown;
    }
}
