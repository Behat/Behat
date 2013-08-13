<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Feature event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureEvent extends Event implements LifecycleEventInterface
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
     * @var null|integer
     */
    private $result;

    /**
     * Initializes feature event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param FeatureNode          $feature
     * @param integer              $result
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        FeatureNode $feature,
        $result = null
    )
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->feature = $feature;
        $this->result = $result;
    }

    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
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

    /**
     * Returns feature tester result code.
     *
     * @return null|integer
     */
    public function getResult()
    {
        return $this->result;
    }
}
