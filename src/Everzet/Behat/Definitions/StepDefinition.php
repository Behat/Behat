<?php

namespace Everzet\Behat\Definitions;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step Definition
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepDefinition
{
    protected $type;
    protected $regex;
    protected $matchedText;
    protected $callback;
    protected $file;
    protected $line;
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
        $this->type = $type;
        $this->regex = $regex;
        $this->callback = $callback;
        $this->file = $file;
        $this->line = $line;
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
     * Runs step definition
     *
     * @return  void
     */
    public function run()
    {
        call_user_func_array($this->callback, $this->values);
    }
}
