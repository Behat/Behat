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
    private $regex;

    /**
     * Initializes transformation.
     *
     * @param   callback    $callback   definition callback
     * @param   string      $regex      definition regular expression
     */
    public function __construct($callback, $regex)
    {
        parent::__construct($callback);

        if (!$this->isClosure()) {
            $methodRefl = new \ReflectionMethod($callback[0], $callback[1]);

            if (!is_callable($callback)) {
                throw new \InvalidArgumentException('Callback should be valid callable');
            }

            if (!$methodRefl->isStatic()) {
                throw new \InvalidArgumentException('Transformation callbacks should be static methods');
            }
        }

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
        $callback = $this->getCallback();
        if (!$this->isClosure()) {
            $callback = array($context->getContextByClassName($callback[0]), $callback[1]);
        }

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
