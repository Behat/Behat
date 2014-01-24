<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject;

use ArrayIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork array subject iterator.
 *
 * Return instance of this class from locator if subjects cannot be searched lazily
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ArraySubjectIterator extends ArrayIterator implements SubjectIterator
{
    /**
     * @var Suite
     */
    private $suite;

    public function __construct(Suite $suite, $array = array())
    {
        $this->suite = $suite;
        parent::__construct($array);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->suite;
    }
}
