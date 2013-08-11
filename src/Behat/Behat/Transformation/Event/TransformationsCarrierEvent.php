<?php

namespace Behat\Behat\Transformation\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Transformation\TransformationInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Transformation carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TransformationsCarrierEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var TransformationInterface[]
     */
    private $transformations = array();

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
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
     * Returns context pool.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Adds transformation to dispatcher.
     *
     * @param TransformationInterface $transformation
     */
    public function addTransformation(TransformationInterface $transformation)
    {
        $regex = $transformation->getRegex();

        $this->transformations[$regex] = $transformation;
    }

    /**
     * Returns all added transformations.
     *
     * @return TransformationInterface[]
     */
    public function getTransformations()
    {
        return $this->transformations;
    }
}
