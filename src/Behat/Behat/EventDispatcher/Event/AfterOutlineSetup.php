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
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param Setup       $setup
     */
    public function __construct(Environment $env, FeatureNode $feature, OutlineNode $outline, Setup $setup)
    {
        parent::__construct($env);

        $this->feature = $feature;
        $this->outline = $outline;
        $this->setup = $setup;
    }

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
    }

    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
