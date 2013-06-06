<?php

namespace Behat\Behat\Gherkin\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Gherkin\Loader\AbstractFileLoader,
    Behat\Gherkin\Gherkin;

use Symfony\Component\Finder\Finder;

/**
 * Gherkin loader with features/ path support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureSuiteLoader extends AbstractFileLoader
{
    private $featuresPath;
    private $gherkin;

    /**
     * Initializes loader.
     *
     * @param string  $featuresPath
     * @param Gherkin $gherkin
     */
    public function __construct($featuresPath, Gherkin $gherkin)
    {
        $this->featuresPath = $featuresPath;
        $this->gherkin      = $gherkin;
    }

    /**
     * Checks if current loader supports provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return Boolean
     */
    public function supports($resource)
    {
        return '' === $resource
            && is_dir($this->featuresPath);
    }

    /**
     * Loads features from provided resource.
     *
     * @param mixed $resource Resource to load
     *
     * @return array
     */
    public function load($resource)
    {
        if (!$this->featuresPath || !file_exists($this->featuresPath)) {
            return array();
        }

        $iterator = Finder::create()
            ->depth(0)
            ->followLinks()
            ->sortByName()
            ->in($this->featuresPath)
        ;

        $features = array();
        foreach ($iterator as $path) {
            $resource = (string) $path;
            $loader   = $this->gherkin->resolveLoader($resource);

            if (null !== $loader) {
                $features = array_merge($features, $loader->load($resource));
            }
        }

        return $features;
    }
}
