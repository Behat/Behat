<?php

namespace Behat\Behat\Definition;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Exception\Error,
    Behat\Behat\Environment\EnvironmentInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step definition.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Definition
{
    /**
     * Step type (Given|When|Then|...)
     *
     * @var     string
     */
    protected $type;
    /**
     * Definition regex to match.
     *
     * @var     string
     */
    protected $regex;
    /**
     * Definition callback.
     *
     * @var     \Callback
     */
    protected $callback;
    /**
     * Definition filename.
     *
     * @var     string
     */
    protected $file;
    /**
     * Definition lineno.
     *
     * @var     integer
     */
    protected $line;
    /**
     * Matched to definition regex text.
     *
     * @var     string
     */
    protected $matchedText;
    /**
     * Step parameters for call.
     *
     * @var     array
     */
    protected $values = array();

    /**
     * Initialize definition.
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
     * Return step definition type (Given|When|Then|...).
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return definition regex to match.
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Set text from matched step.
     *
     * @param   string  $text   step text (description)
     */
    public function setMatchedText($text)
    {
        $this->matchedText = $text;
    }

    /**
     * Return text from matched step.
     *
     * @return  string
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Return definition filename.
     *
     * @return  string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Return definition line number.
     *
     * @return  integer
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Set step parameters for call.
     *
     * @param   array   $values step parameters
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * Custom error handler.
     *
     * @see     set_error_handler
     *
     * @throws  \Behat\Behat\Exception\Error
     */
    public function errorHandler($code, $message, $file, $line)
    {
        throw new Error($code, $message, $file, $line);
    }

    /**
     * Runs step definition.
     *
     * @param   EnvironmentInterface    $environment    runners shared environment
     *
     * @throws  \Behat\Behat\Exception\BehaviorException
     */
    public function run(EnvironmentInterface $environment, $tokens = array())
    {
        $oldHandler = set_error_handler(array($this, 'errorHandler'), E_ALL ^ E_WARNING);
        $values     = $this->values;

        if (count($tokens)) {
            foreach ($values as $i => $value) {
                if ($value instanceof TableNode || $value instanceof PyStringNode) {
                    $values[$i] = clone $value;
                    $values[$i]->replaceTokens($tokens);
                }
            }
        }

        array_unshift($values, $environment);
        call_user_func_array($this->callback, $values);

        if (null !== $oldHandler) {
            set_error_handler($oldHandler);
        }
    }
}
