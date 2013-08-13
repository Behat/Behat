<?php

namespace Behat\Behat\Definition\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Callee;
use Behat\Behat\Definition\DefinitionInterface;

/**
 * Base definition callee class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Definition extends Callee implements DefinitionInterface
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $regex;

    /**
     * Initializes definition.
     *
     * @param string      $type
     * @param string      $regex
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($type, $regex, $callback, $description = null)
    {
        $this->type = $type;
        $this->regex = $regex;

        parent::__construct($callback, $description);
    }

    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns regular expression.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }
}
