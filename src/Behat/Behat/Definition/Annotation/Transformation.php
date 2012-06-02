<?php

namespace Behat\Behat\Definition\Annotation;

use Behat\Gherkin\Node\TableNode;

use Behat\Behat\Definition\TransformationInterface,
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
 * Step arguments transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation extends Annotation implements TransformationInterface
{
    private $regex;

    /**
     * Initializes transformation.
     *
     * @param callback $callback definition callback
     * @param string   $regex    definition regular expression
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($callback, $regex)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf(
                'Transformation callback should be a valid callable, but %s given',
                gettype($callback)
            ));
        }
        parent::__construct($callback);

        $this->regex = $regex;
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

    /**
     * Transforms provided argument.
     *
     * @param string           $translatedRegex
     * @param ContextInterface $context
     * @param mixed            $argument
     *
     * @return mixed
     */
    public function transform($translatedRegex, ContextInterface $context, $argument)
    {
        $callback = $this->getCallbackForContext($context);

        if ($argument instanceof TableNode) {
            $tableMatching = 'table:' . implode(',', $argument->getRow(0));

            if (preg_match($translatedRegex, $tableMatching)
             || preg_match($this->regex, $tableMatching)) {
                return call_user_func($callback, $argument);
            }
        } elseif (is_string($argument) || $argument instanceof PyStringNode) {
            if (preg_match($translatedRegex, (string) $argument, $transformArguments)
             || preg_match($this->regex, (string) $argument, $transformArguments)) {
                array_shift($transformArguments);

                return call_user_func_array($callback, $transformArguments);
            }
        }
    }
}
