<?php

namespace Behat\Behat\Definition\Annotation;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Exception\ErrorException,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Annotation\Annotation;

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
abstract class Definition extends Annotation implements DefinitionInterface
{
    /**
     * Definition regex to match.
     *
     * @var     string
     */
    private $regex;
    /**
     * Matched to definition regex text.
     *
     * @var     string
     */
    private $matchedText;
    /**
     * Step parameters for call.
     *
     * @var     array
     */
    private $values = array();

    /**
     * Initializes definition.
     *
     * @param   callback    $callback   definition callback
     * @param   string      $regex      definition regular expression
     */
    public function __construct($callback, $regex)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" definition callback should be a valid callable, but %s given',
                basename(str_replace('\\', '/', get_class($this))), gettype($callback)
            ));
        }
        parent::__construct($callback);

        $this->regex = $regex;
    }

    /**
     * Returns definition regex to match.
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Saves matched step text to definition.
     *
     * @param   string  $text   step text (description)
     */
    public function setMatchedText($text)
    {
        $this->matchedText = $text;
    }

    /**
     * Returns matched step text.
     *
     * @return  string
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Sets step parameters for step run.
     *
     * @param   array   $values step parameters
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * Returns step parameters for step run.
     *
     * @return  array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Custom error handler.
     *
     * This method used as custom error handler when step is running.
     *
     * @see     set_error_handler()
     *
     * @throws  Behat\Behat\Exception\ErrorException
     */
    public function errorHandler($code, $message, $file, $line)
    {
        if (0 === error_reporting()) {
            return; // error reporting turned off or more likely suppressed with @
        }
        throw new ErrorException($code, $message, $file, $line);
    }

    /**
     * @see     Behat\Behat\Definition\DefinitionInterface::run()
     */
    public function run(ContextInterface $context, $tokens = array())
    {
        if (defined('BEHAT_ERROR_REPORTING')) {
            $errorLevel = BEHAT_ERROR_REPORTING;
        } else {
            $errorLevel = E_ALL ^ E_WARNING;
        }

        $oldHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);
        $callback   = $this->getCallbackForContext($context);

        $values = $this->getValues();
        if (count($tokens)) {
            foreach ($values as $i => $value) {
                if ($value instanceof TableNode || $value instanceof PyStringNode) {
                    $values[$i] = clone $value;
                    $values[$i]->replaceTokens($tokens);
                }
            }
        }
        if ($this->isClosure()) {
            array_unshift($values, $context);
        }

        $return = call_user_func_array($callback, $values);

        if (null !== $oldHandler) {
            set_error_handler($oldHandler);
        }

        return $return;
    }
}
