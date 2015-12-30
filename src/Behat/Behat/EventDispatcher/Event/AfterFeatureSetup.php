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
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after feature is setup for a test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterFeatureSetup extends FeatureTested implements AfterSetup
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param SpecificationContext $context
     * @param Setup                $setup
     */
    public function __construct(SpecificationContext $context, Setup $setup)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getSpecification();
        $this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::AFTER_SETUP;
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
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
