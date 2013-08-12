<?php

namespace Behat\Behat\Features\Loader;

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
 * Loader interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Checks if loader supports provided suite & locator.
     *
     * @param SuiteInterface $suite
     * @param string         $locator
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, $locator);

    /**
     * Loads features using provided suite & locator.
     *
     * @param SuiteInterface $suite
     * @param string         $locator
     *
     * @return FeatureNode[]
     */
    public function load(SuiteInterface $suite, $locator);
}
