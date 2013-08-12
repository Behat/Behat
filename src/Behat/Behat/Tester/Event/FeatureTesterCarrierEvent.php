<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ContextualTesterCarrierEvent;
use Behat\Gherkin\Node\FeatureNode;

/**
 * Feature tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTesterCarrierEvent extends ContextualTesterCarrierEvent
{
    /**
     * @var FeatureNode
     */
    private $feature;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param FeatureNode          $feature
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, FeatureNode $feature)
    {
        parent::__construct($suite, $contexts);

        $this->feature = $feature;
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
