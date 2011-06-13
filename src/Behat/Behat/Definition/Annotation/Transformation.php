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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation extends Annotation implements TransformationInterface
{
    /**
     * Transformation regex.
     *
     * @var     string
     */
    protected $regex;

    /**
     * Initializes transformation.
     *
     * @param   array   $callback   transformation callback
     * @param   string  $regex      transformation regular expression
     */
    public function __construct(array $callback, $regex)
    {
        parent::__construct($callback);

        $this->regex = $regex;
    }

    /**
     * Returns transformation regex.
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @see     Behat\Behat\Definition\TransformationInterface::transform()
     */
    public function transform(ContextInterface $context, $argument)
    {
        $callback = array($context, $this->getMethod());

        if ($argument instanceof TableNode) {
            $tableMatching = 'table:' . implode(',', $argument->getRow(0));

            if (preg_match($this->regex, $tableMatching)) {
                return call_user_func($callback, $argument);
            }
        } elseif (is_string($argument) || $argument instanceof PyStringNode) {
            if (preg_match($this->regex, (string) $argument, $transformArguments)) {
                return call_user_func(
                    $callback, $transformArguments[1 === count($transformArguments) ? 0 : 1]
                );
            }
        }

        return false;
    }
}
