<?php

namespace Everzet\Behat\Definition;

use Everzet\Gherkin\Element\Inline\PyStringElement;
use Everzet\Gherkin\Element\Inline\TableElement;

use Everzet\Behat\Exception\Error;
use Everzet\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step Definition holder.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepDefinition
{
    protected $type;
    protected $regex;
    protected $callback;
    protected $file;
    protected $line;
    protected $matchedText;
    protected $values = array();

    /**
     * Constructs new Step Definition
     *
     * @param   string      $type       step type (Given/When/Then/And or localized one)
     * @param   string      $regex      step matching regular expression
     * @param   callback    $callback   step callback
     * @param   string      $file       step definition file
     * @param   integer     $line       step definition line
     */
    public function __construct($type, $regex, $callback, $file = null, $line = null)
    {
        $this->type         = $type;
        $this->regex        = $regex;
        $this->callback     = $callback;
        $this->file         = $file;
        $this->line         = $line;
    }

    /**
     * Returns step type (Given/When/Then/And or localized one)
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns step matching regular expression
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Sets step text from matched element
     *
     * @param   string  $text   step text (description)
     */
    public function setMatchedText($text)
    {
        $this->matchedText = $text;
    }

    /**
     * Returns step text from matched element
     *
     * @return  string
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Returns step definition file path
     *
     * @return  string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns step definition line number
     *
     * @return  integer
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Sets step parameters for call
     *
     * @param   array   $values step parameters
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * Custom Error handler
     *
     * @see     set_error_handler
     * 
     * @throws  Everzet\Behat\Exception\Error   that incapsulates error information
     */
    public function errorHandler($code, $message, $file, $line)
    {
        throw new Error($code, $message, $file, $line);
    }

    /**
     * Runs step definition
     *
     * @param   EnvironmentInterface    $environment    runners shared environment
     *
     * @return  void
     * 
     * @throws  Everzet\Behat\Exception\BehaviorException
     */
    public function run(EnvironmentInterface $environment)
    {
        $oldHandler = set_error_handler(array($this, 'errorHandler'), E_ALL);

        $values = $this->values;
        array_unshift($values, $environment);
        call_user_func_array($this->callback, array_map(function($value) {
            if ($value instanceof PyStringElement) {
                return (string) $value;
            } elseif ($value instanceof TableElement) {
                return $value->getHash();
            } else {
                return $value;
            }
        }, $values));

        if (null !== $oldHandler) {
            set_error_handler($oldHandler);
        }
    }
}
