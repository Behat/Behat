<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Specification;

use Behat\Gherkin\Filter\FilterInterface;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Suite;

/**
 * Lazily iterates (parses one-by-one) over features.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class LazyFeatureIterator implements SpecificationIterator
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
     * Initializes specifications.
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
        $this->filters = array_merge($this->getSuiteFilters($suite), $filters);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
        $this->moveToNextAvailableFeature();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->moveToNextAvailableFeature();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== $this->currentFeature;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->currentFeature;
    }

    /**
     * Returns list of filters from suite settings.
     *
     * @param Suite $suite
     *
     * @return FilterInterface[]
     */
    private function getSuiteFilters(Suite $suite)
    {
        if (!$suite->hasSetting('filters') || !is_array($suite->getSetting('filters'))) {
            return array();
        }

        $filters = array();
        foreach ($suite->getSetting('filters') as $type => $filterString) {
            $filters[] = $this->createFilter($type, $filterString, $suite);
        }

        return $filters;
    }

    /**
     * Creates filter of provided type.
     *
     * @param string $type
     * @param string $filterString
     * @param Suite  $suite
     *
     * @return FilterInterface
     *
     * @throws SuiteConfigurationException If filter type is not recognised
     */
    private function createFilter($type, $filterString, Suite $suite)
    {
        if ('role' === $type) {
            return new RoleFilter($filterString);
        }

        if ('name' === $type) {
            return new NameFilter($filterString);
        }

        if ('tags' === $type) {
            return new TagFilter($filterString);
        }

        throw new SuiteConfigurationException(sprintf(
            '`%s` filter is not supported by the `%s` suite. Supported types are %s.',
            $type,
            $suite->getName(),
            implode(', ', array('`role`', '`name`', '`tags`'))
        ), $suite->getName());
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
