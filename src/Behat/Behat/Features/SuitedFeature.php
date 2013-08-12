<?php

namespace Behat\Behat\Features;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\FeatureNode;

/**
 * Suited feature.
 * Stores both feature and suite this feature was loaded with.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuitedFeature
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var FeatureNode
     */
    private $feature;

    /**
     * Initializes suited feature.
     *
     * @param SuiteInterface $suite
     * @param FeatureNode    $feature
     */
    public function __construct(SuiteInterface $suite, FeatureNode $feature)
    {
        $this->suite = $suite;
        $this->feature = $feature;
    }

    /**
     * Returns suite.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns feature node.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
