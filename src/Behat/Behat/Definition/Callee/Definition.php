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
use Behat\Behat\Snippet\Util\Turnip;

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
    private $pattern;
    /**
     * @var string
     */
    private $regex;

    /**
     * Initializes definition.
     *
     * @param string      $type
     * @param string      $pattern
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($type, $pattern, $callback, $description = null)
    {
        $this->type = $type;
        $this->pattern = $pattern;

        $this->regex = $pattern;
        // If it is a turnip pattern - transform it to regex
        if ('/' !== substr($pattern, 0, 1)) {
            $this->regex = Turnip::turnipToRegex($pattern);
        }

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
     * Returns step pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
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

    /**
     * Represents definition as a string.
     *
     * @return string
     */
    public function toString()
    {
        return $this->getType() . ' ' . $this->getPattern();
    }
}
