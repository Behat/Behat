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
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after outline setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterOutlineSetup extends OutlineTested implements AfterSetup
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly OutlineNode $outline,
        private readonly Setup $setup,
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
     * Returns current test setup.
     */
    public function getSetup(): Setup
    {
        return $this->setup;
    }
}
