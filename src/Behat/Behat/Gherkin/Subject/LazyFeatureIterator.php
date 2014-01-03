<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Subject;

use Behat\Gherkin\Filter\FilterInterface;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Behat lazy feature iterator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LazyFeatureIterator implements SubjectIterator
{
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var Gherkin
     */
    private $gherkin;
    /**
     * @var string[]
     */
    private $paths = array();
    /**
     * @var FilterInterface[]
     */
    private $filters = array();
    /**
     * @var integer
     */
    private $position = 0;
    /**
     * @var FeatureNode[]
     */
    private $features = array();
    /**
     * @var FeatureNode
     */
    private $currentFeature;

    /**
     * Initializes subjects.
     *
     * @param Suite             $suite
     * @param Gherkin           $gherkin
     * @param string[]          $paths
     * @param FilterInterface[] $filters
     */
    public function __construct(Suite $suite, Gherkin $gherkin, array $paths, array $filters = array())
    {
        $this->suite = $suite;
        $this->gherkin = $gherkin;
        $this->paths = array_values($paths);
        $this->filters = $filters;
    }

    /**
     * Returns suite that was used to load subjects.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Rewinds the Iterator to the first element.
     */
    public function rewind()
    {
        $this->position = 0;
        $this->moveToNextAvailableFeature();
    }

    /**
     * Moves forward to the next element.
     */
    public function next()
    {
        $this->moveToNextAvailableFeature();
    }

    /**
     * Checks if current position is valid.
     *
     * @return Boolean
     */
    public function valid()
    {
        return null !== $this->currentFeature;
    }

    /**
     * Returns the key of the current element.
     *
     * @return string
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Returns the current element.
     *
     * @return null|FeatureNode
     */
    public function current()
    {
        return $this->currentFeature;
    }

    /**
     * Parses paths consequently.
     */
    private function moveToNextAvailableFeature()
    {
        while (!count($this->features) && $this->position < count($this->paths)) {
            $this->features = $this->parseFeature($this->paths[$this->position]);
            $this->position++;
        }

        $this->currentFeature = array_shift($this->features);
    }

    /**
     * Parses feature at path.
     *
     * @param string $path
     *
     * @return FeatureNode[]
     */
    private function parseFeature($path)
    {
        return $this->gherkin->load($path, $this->filters);
    }
}
