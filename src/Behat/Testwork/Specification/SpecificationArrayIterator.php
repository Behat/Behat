<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use ArrayIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Iterates over specifications array.
 *
 * Return instance of this class from locator if specifications cannot be searched lazily.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @template T
 * @implements SpecificationIterator<T>
 * @extends ArrayIterator<int, T>
 */
final class SpecificationArrayIterator extends ArrayIterator implements SpecificationIterator
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * Initializes iterator.
     *
     * @param Suite         $suite
     * @param array<int, T> $specifications
     */
    public function __construct(Suite $suite, $specifications = array())
    {
        $this->suite = $suite;

        parent::__construct($specifications);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->suite;
    }
}
