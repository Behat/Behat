<?php

namespace Behat\Behat\Features\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Features\SuitedFeature;
use Symfony\Component\EventDispatcher\Event;

/**
 * Features carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesCarrierEvent extends Event implements EventInterface
{
    /**
     * @var string
     */
    private $locator;
    /**
     * @var string
     */
    private $suiteName;
    /**
     * @var SuitedFeature[]
     */
    private $features = array();

    /**
     * Initializes event.
     *
     * @param null|string $locator
     * @param null|string $suiteName
     */
    public function __construct($locator = null, $suiteName = null)
    {
        $this->locator = $locator;
        $this->suiteName = $suiteName;
    }

    /**
     * Returns features locator.
     *
     * @return null|string
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Checks if event stores a suite name.
     *
     * @return Boolean
     */
    public function hasSuiteName()
    {
        return null !== $this->suiteName;
    }

    /**
     * Returns suite name.
     *
     * @return null|string
     */
    public function getSuiteName()
    {
        return $this->suiteName;
    }

    /**
     * Adds feature to the carrier.
     *
     * @param SuitedFeature $feature
     */
    public function addFeature(SuitedFeature $feature)
    {
        $this->features[] = $feature;
    }

    /**
     * Returns all features stored in carrier.
     *
     * @return SuitedFeature[]
     */
    public function getFeatures()
    {
        return $this->features;
    }
}
