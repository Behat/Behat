<?php

namespace Behat\Behat\Definition\Loader;

use Behat\Gherkin\Node\StepNode;

use Behat\Behat\Definition\DefinitionDispatcher,
    Behat\Behat\Definition\Definition,
    Behat\Behat\Definition\Transformation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP-files definitions loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PhpFileLoader implements LoaderInterface
{
    /**
     * Definition dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    protected $dispatcher;
    /**
     * Found objects (Definition's & Transformation's)
     *
     * @var     array
     */
    protected $objects = array();

    /**
     * Initializes loader.
     *
     * @param   DefinitionDispatcher $dispatcher definition dispatcher
     */
    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource)
    {
        $this->objects  = array();
        $steps          = $this;

        require_once($resource);

        return $this->objects;
    }

    /**
     * Defines argument transformation.
     *
     * @param   string      $regex      transformation regex (to find specific argument)
     * @param   Callback    $callback   transformation callback (must return transformed argument)
     */
    public function Transform($regex, $callback)
    {
        $this->objects[] = new Transformation($regex, $callback);
    }

    /**
     * Defines a step with ->Given|When|Then|...('/regex/', callback) or
     * call a step with ->Given|When|Then|...('I enter "12" in the field', $world) or
     * even with arguments ->Given|When|Then|...('I fill up fields', $world, $table).
     *
     * @param   string  $type       step type (Given|When|Then|...)
     * @param   string  $arguments  step regex & callback
     *
     * @throws  Behat\Behat\Exception\Redundant     if step definition is already exists
     */
    public function __call($type, $arguments)
    {
        if (2 == count($arguments) && is_callable($arguments[1])) {
            $debug = debug_backtrace();
            $debug = $debug[1];

            $this->objects[] = new Definition(
                $type, $arguments[0], $arguments[1], $debug['file'], $debug['line']
            );
        } else {
            $text   = array_shift($arguments);
            $world  = array_shift($arguments);

            $step   = new StepNode($type, $text);
            $step->setArguments($arguments);

            $definition = $this->dispatcher->findDefinition($step);
            $definition->run($world);
        }

        return $this;
    }
}
