<?php

namespace Behat\Behat\Context\Loader;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\AnnotatedContextInterface,
    Behat\Behat\Definition\DefinitionDispatcher,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Definition\TransformationInterface,
    Behat\Behat\Hook\HookDispatcher,
    Behat\Behat\Hook\HookInterface,
    Behat\Behat\Annotation\AnnotationInterface;

use Symfony\Component\Translation\TranslatorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Annotated contexts reader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AnnotatedContextLoader implements ContextLoaderInterface
{
    /**
     * Step definitions dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    private $definitionDispatcher;
    /**
     * Hooks dispatcher.
     *
     * @var     Behat\Behat\Hook\HookDispatcher
     */
    private $hookDispatcher;
    /**
     * Translator.
     *
     * @var     Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;
    /**
     * List of available annotations.
     *
     * @var     array
     */
    private $annotationClasses = array(
        'given'          => 'Behat\Behat\Definition\Annotation\Given',
        'when'           => 'Behat\Behat\Definition\Annotation\When',
        'then'           => 'Behat\Behat\Definition\Annotation\Then',
        'transform'      => 'Behat\Behat\Definition\Annotation\Transformation',
        'beforesuite'    => 'Behat\Behat\Hook\Annotation\BeforeSuite',
        'aftersuite'     => 'Behat\Behat\Hook\Annotation\AfterSuite',
        'beforefeature'  => 'Behat\Behat\Hook\Annotation\BeforeFeature',
        'afterfeature'   => 'Behat\Behat\Hook\Annotation\AfterFeature',
        'beforescenario' => 'Behat\Behat\Hook\Annotation\BeforeScenario',
        'afterscenario'  => 'Behat\Behat\Hook\Annotation\AfterScenario',
        'beforestep'     => 'Behat\Behat\Hook\Annotation\BeforeStep',
        'afterstep'      => 'Behat\Behat\Hook\Annotation\AfterStep'
    );
    /**
     * String with all available annotations, splitted with "|".
     *
     * @var     string
     */
    private $availableAnnotations;

    /**
     * Initializes context loader.
     *
     * @param   Behat\Behat\Definition\DefinitionDispatcher         $definitionDispatcher   definitions
     * @param   Behat\Behat\Hook\HookDispatcher                     $hookDispatcher         hooks
     * @param   Symfony\Component\Translation\TranslatorInterface   $translator             translator
     */
    public function __construct(DefinitionDispatcher $definitionDispatcher, HookDispatcher $hookDispatcher, 
                                TranslatorInterface $translator)
    {
        $this->definitionDispatcher = $definitionDispatcher;
        $this->hookDispatcher       = $hookDispatcher;
        $this->translator           = $translator;
        $this->availableAnnotations = implode("|", array_keys($this->annotationClasses));
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::supports()
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof AnnotatedContextInterface;
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::load()
     */
    public function load(ContextInterface $context)
    {
        $this->readDefinitions($context);
        $this->readTranslations($context);
    }

    /**
     * Reads annotated context annotations.
     *
     * @param   Behat\Behat\Context\AnnotatedContextInterface   $context
     */
    private function readDefinitions(AnnotatedContextInterface $context)
    {
        $reflection = new \ReflectionObject($context);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRefl) {
            foreach ($this->readMethodAnnotations($reflection->getName(), $methodRefl) as $annotation) {
                if ($annotation instanceof DefinitionInterface) {
                    $this->definitionDispatcher->addDefinition($annotation);
                } elseif ($annotation instanceof TransformationInterface) {
                    $this->definitionDispatcher->addTransformation($annotation);
                } elseif ($annotation instanceof HookInterface) {
                    $this->hookDispatcher->addHook($annotation);
                }
            }
        }
    }

    /**
     * Reads annotated context translations.
     *
     * @param   Behat\Behat\Context\AnnotatedContextInterface   $context
     */
    private function readTranslations(AnnotatedContextInterface $context)
    {
        foreach ($context->getI18nResources() as $path) {
            $this->translator->addResource('xliff', $path, basename($path, '.xliff'), 'behat.definitions');
        }
    }

    /**
     * Reads all supported method annotations.
     *
     * @param   stirng              $className  method class name
     * @param   ReflectionMethod    $method     method reflection
     */
    private function readMethodAnnotations($className, \ReflectionMethod $method)
    {
        $annotations = array();

        // read parent annotations
        try {
            $prototype = $method->getPrototype();
            $annotations = array_merge($annotations, $this->readMethodAnnotations($className, $prototype));
        } catch (\ReflectionException $e) {}

        // read method annotations
        if ($docBlock = $method->getDocComment()) {
            foreach (explode("\n", $docBlock) as $docLine) {
                $docLine = preg_replace('/^\/\*\*\s*|^\s*\*\s*|\s*\*\/$|\s*$/', '', $docLine);

                if (preg_match('/^\@('.$this->availableAnnotations.')\s*(?:(.*))?$/i', $docLine, $matches)) {
                    $class    = $this->annotationClasses[strtolower($matches[1])];
                    $callback = array($className, $method->getName());

                    if (isset($matches[2]) && !empty($matches[2])) {
                        $annotations[] = new $class($callback, $matches[2]);
                    } else {
                        $annotations[] = new $class($callback);
                    }
                }
            }
        }

        return $annotations;
    }
}
