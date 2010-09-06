<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\Finder\Finder;

use Everzet\Gherkin\Element\StepElement;
use Everzet\Gherkin\Element\Inline\PyStringElement;
use Everzet\Gherkin\Element\Inline\TableElement;

use Everzet\Behat\Definition\StepDefinition;
use Everzet\Behat\Environment\EnvironmentInterface;
use Everzet\Behat\Exception\Redundant;
use Everzet\Behat\Exception\Ambiguous;
use Everzet\Behat\Exception\Undefined;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Steps Container
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepsLoader
{
    protected $steps = array();

    /**
     * Creates steps container & load step definitions
     *
     * @param   \Iterator   $definitionFiles    step definition files
     * @param   World       $world              world object instance
     */
    public function __construct($path, EnvironmentInterface $world = null)
    {
        $steps = $this;

        $finder = new Finder();
        foreach ($finder->files()->name('*.php')->in($path) as $definitionFile) {
            require $definitionFile;
        }
    }

    /**
     * Define a step with ->Given('/regex/', callback)
     *
     * @param   string  $type       step type (Given/When/Then/And or localized one)
     * @param   string  $arguments  step regex & callback
     * 
     * @throws  \Everzet\Behat\Exceptions\Redundant if step definition is already exists
     */
    public function __call($type, $arguments)
    {
        $debug = debug_backtrace();
        $debug = $debug[1];

        $definition = new StepDefinition(
            $type, $arguments[0], $arguments[1], $debug['file'], $debug['line']
        );

        if (isset($this->steps[$definition->getRegex()])) {
            throw new Redundant(
                $definition, $this->steps[$definition->getRegex()]
            );
        }

        $this->steps[$definition->getRegex()] = $definition;

        return $this;
    }

    /**
     * Translates step description into definition
     *
     * @param   Step    $step   step instance
     * @param   string  $text   step test
     * 
     * @return  string          definition
     */
    public function proposeDefinition(StepElement $step)
    {
        $text   = $step->getText();
        $args   = $step->getArguments();

        $regexp = preg_replace(
            array('/\"([^\"]*)\"/', '/(\d+)/'), array("\"([^\"]*)\"", "(\\d+)"), $text, -1, $count
        );

        $args = array();
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg".($i + 1);
        }

        foreach ($args as $argument) {
            if ($argument instanceof PyStringElement) {
                $args[] = "\$string";
            } elseif ($argument instanceof TableElement) {
                $args[] = "\$table";
            }
        }

        $description = sprintf(<<<PHP
\$steps->%s('/^%s$/', function(%s) use(\$world) {
    throw new \Everzet\Behat\Exception\Pending();
});
PHP
          , '%s', $regexp, implode(', ', $args)
        );

        return array(md5($description) => sprintf($description, $step->getType()));
    }

    /**
     * Finds & returns step definition, that matches specific step description
     *
     * @param   Step            $step       specific step to match
     * @param   array           $examples   examples tokens to replace description placeholders
     * 
     * @return  StepDefinition
     * 
     * @throws  \Everzet\Behat\Exceptions\Ambiguous if step description is ambiguous
     * @throws  \Everzet\Behat\Exceptions\Undefined if step definition not found
     */
    public function findDefinition(StepElement $step)
    {
        $text       = $step->getText();
        $args       = $step->getArguments();
        $matches    = array();

        foreach ($this->steps as $regex => $definition) {
            if (preg_match($regex, $text, $values)) {
                $definition->setMatchedText($text);
                $definition->setValues(array_merge(array_slice($values, 1), $args));
                $matches[] = $definition;
            }
        }

        if (count($matches) > 1) {
            throw new Ambiguous($text, $matches);
        }

        if (0 === count($matches)) {
            throw new Undefined($text);
        }

        return $matches[0];
    }
}
