<?php

namespace Behat\Behat\Suite;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Filter\FilterInterface;

/**
 * Basic suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SuiteInterface
{
    /**
     * Returns unique ID of this suite.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns suite name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns list of feature locators.
     * Conventionally, locators that are not paths in the local filesystem should start with `@` sign
     * followed by the ID of a remote storage.
     *
     * @return string[]
     */
    public function getFeatureLocators();

    /**
     * Returns feature filters.
     *
     * @return FilterInterface[]
     */
    public function getFeatureFilters();

    /**
     * Returns context class names.
     *
     * @return string[]
     */
    public function getContextClasses();

    /**
     * Returns parameters.
     *
     * @return array
     */
    public function getParameters();
}
