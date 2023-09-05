<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Suite\Suite;

/**
 * Iterates over specifications array.
 *
 * Return instance of this class from locator if specifications cannot be searched lazily.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
final class SpecificationArrayIterator extends \ArrayIterator implements SpecificationIterator
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * Initializes iterator.
     *
     * @param mixed[] $specifications
     */
    public function __construct(Suite $suite, $specifications = [])
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
