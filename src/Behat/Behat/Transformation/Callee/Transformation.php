<?php

namespace Behat\Behat\Transformation\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Callee;
use Behat\Behat\Transformation\TransformationInterface;

/**
 * Step transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation extends Callee implements TransformationInterface
{
    /**
     * @var string
     */
    private $regex;

    /**
     * Initializes transformation.
     *
     * @param string      $regex
     * @param Callable    $callable
     * @param null|string $description
     */
    public function __construct($regex, $callable, $description = null)
    {
        $this->regex = $regex;

        parent::__construct($callable, $description);
    }

    /**
     * Returns transformation regex.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }
}
