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
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event right before outline teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeOutlineTeardown extends OutlineTested implements BeforeTeardown
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly OutlineNode $outline,
        private readonly TestResult $result,
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
}
