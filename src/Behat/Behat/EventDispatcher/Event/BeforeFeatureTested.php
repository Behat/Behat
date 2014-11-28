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
use Behat\Testwork\EventDispatcher\Event\BeforeTested;
use Behat\Testwork\Tester\Context\SpecificationContext;

/**
 * Represents an event before feature tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeFeatureTested extends FeatureTested implements BeforeTested
{
    /**
     * @var FeatureNode
     */
    private $feature;

    /**
     * Initializes event.
     *
     * @param SpecificationContext $context
     */
    public function __construct(SpecificationContext $context)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getSpecification();
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
}
