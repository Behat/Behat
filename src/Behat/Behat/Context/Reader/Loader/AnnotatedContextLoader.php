<?php

namespace Behat\Behat\Context\Reader\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Suite\SuiteInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Annotated context loader.
 * Loads context callees from annotated methods.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AnnotatedContextLoader implements LoaderInterface
{
    /**
     * @var string[string]
     */
    private $stepClasses = array(
        'given'     => 'Behat\Behat\Definition\Callee\Given',
        'when'      => 'Behat\Behat\Definition\Callee\When',
        'then'      => 'Behat\Behat\Definition\Callee\Then',
        'transform' => 'Behat\Behat\Transformation\Callee\Transformation',
    );
    /**
     * @var string[string]
     */
    private $hookClasses = array(
        'beforesuite' => 'Behat\Behat\Hook\Callee\BeforeSuite',
        'aftersuite'  => 'Behat\Behat\Hook\Callee\AfterSuite',
    );
    /**
     * @var string[string]
     */
    private $filterableHookClasses = array(
        'beforefeature'  => 'Behat\Behat\Hook\Callee\BeforeFeature',
        'afterfeature'   => 'Behat\Behat\Hook\Callee\AfterFeature',
        'beforescenario' => 'Behat\Behat\Hook\Callee\BeforeScenario',
        'afterscenario'  => 'Behat\Behat\Hook\Callee\AfterScenario',
        'beforestep'     => 'Behat\Behat\Hook\Callee\BeforeStep',
        'afterstep'      => 'Behat\Behat\Hook\Callee\AfterStep'
    );
    /**
     * @var string
     */
    private $stepAnnotationsRegex;
    /**
     * @var string
     */
    private $hookAnnotationsRegex;
    /**
     * @var string
     */
    private $filterableHookAnnotationsRegex;

    /**
     * Initializes loader.
     */
    public function __construct()
    {
        $this->stepAnnotationsRegex = implode('|', array_keys($this->stepClasses));
        $this->hookAnnotationsRegex = implode('|', array_keys($this->hookClasses));
        $this->filterableHookAnnotationsRegex = implode('|', array_keys($this->filterableHookClasses));
    }

    /**
     * Loads annotation-based callees from context.
     *
     * @param SuiteInterface $suite
     * @param string         $contextClass
     *
     * @return CalleeInterface[]
     */
    public function loadCallees(SuiteInterface $suite, $contextClass)
    {
        $callees = array();

        $reflection = new ReflectionClass($contextClass);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->loadMethodCallees($reflection->getName(), $method) as $callback) {
                $callees[] = $callback;
            }
        }

        return $callees;
    }

    /**
     * Loads callees associated with specific method.
     *
     * @param string           $class
     * @param ReflectionMethod $method
     *
     * @return CalleeInterface[]
     */
    private function loadMethodCallees($class, ReflectionMethod $method)
    {
        $callees = array();

        // read parent annotations
        try {
            $prototype = $method->getPrototype();
            // error occurs on every second PHP stable release - getPrototype() returns itself
            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $this->loadMethodCallees($class, $prototype));
            }
        } catch (ReflectionException $e) {
        }

        if ($docBlock = $method->getDocComment()) {
            $description = null;

            foreach (explode("\n", $docBlock) as $docLine) {
                $docLine = preg_replace('/^\/\*\*\s*|^\s*\*\s*|\s*\*\/$|\s*$/', '', $docLine);

                if ('' !== trim($docLine) && 0 !== strpos(trim($docLine), '@')) {
                    $description = trim($docLine);

                    continue;
                }

                $callees = array_merge(
                    $callees,
                    $this->loadStepCallees($method, $docLine, $description),
                    $this->loadHookCallees($method, $docLine, $description),
                    $this->loadFilterableHookCallees($method, $docLine, $description)
                );
            }
        }

        return $callees;
    }

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return CalleeInterface[]
     */
    private function loadStepCallees(ReflectionMethod $method, $docLine, $description)
    {
        if (preg_match('/^\@(' . $this->stepAnnotationsRegex . ')\s+(.+)$/i', $docLine, $match)) {
            $class = $this->stepClasses[strtolower($match[1])];
            $regex = $match[2];
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());

            if ('/' !== substr($regex, 0, 1)) {
                $regex = '/^' . preg_quote($regex, '/') . '$/';
            }

            return array(new $class($regex, $callable, $description));
        }

        return array();
    }

    /**
     * Loads hook callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return CalleeInterface[]
     */
    private function loadHookCallees(ReflectionMethod $method, $docLine, $description)
    {
        if (preg_match('/^\@(' . $this->hookAnnotationsRegex . ')$/i', $docLine, $match)) {
            $class = $this->hookClasses[strtolower($match[1])];
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());

            return array(new $class($callable, $description));
        }

        return array();
    }

    /**
     * Loads filterable hook callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return CalleeInterface[]
     */
    private function loadFilterableHookCallees(ReflectionMethod $method, $docLine, $description)
    {
        if (preg_match('/^\@(' . $this->filterableHookAnnotationsRegex . ')\s*(.*)?$/i', $docLine, $match)) {
            $class = $this->filterableHookClasses[strtolower($match[1])];
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());
            $filter = '' !== $match[2] ? $match[2] : null;

            return array(new $class($filter, $callable, $description));
        }

        return array();
    }
}
