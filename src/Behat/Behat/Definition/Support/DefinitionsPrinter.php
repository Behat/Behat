<?php

namespace Behat\Behat\Definition\Support;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Event\ContextPoolCarrierEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Definition\Event\DefinitionsCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Definitions printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionsPrinter extends DispatchingService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes printer.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface      $translator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        parent::__construct($eventDispatcher);

        $this->translator = $translator;
    }

    /**
     * Prints step definitions into console.
     *
     * @param OutputInterface  $output
     * @param SuiteInterface[] $suites
     * @param string           $language
     * @param Boolean          $desc
     */
    public function printDefinitions(OutputInterface $output, array $suites, $language = 'en', $desc = false)
    {
        $output->getFormatter()->setStyle('definition_capture', new OutputFormatterStyle('yellow', null, array('bold')));
        $output->getFormatter()->setStyle('definition_path', new OutputFormatterStyle('black'));

        $output->writeln($this->getDefinitionsText($suites, $language, $desc));
    }

    /**
     * Returns available definitions in string.
     *
     * @param SuiteInterface[] $suites
     * @param string           $language
     * @param bool             $desc
     *
     * @return string
     */
    private function getDefinitionsText(array $suites, $language = 'en', $desc = false)
    {
        $template = "<info>{type}</info> <comment>{regex}</comment>";
        if ($desc) {
            $template .= "\n    {description}<definition_path># {path}</definition_path>\n";
        }

        $output = array();
        foreach ($suites as $suite) {
            $contextPool = $this->createContextPool($suite);
            $definitions = $this->loadDefinitions($suite, $contextPool);

            foreach ($definitions as $definition) {
                $output[] = strtr($template, $this->getDefinitionData($suite, $definition, $language));
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }

    /**
     * Returns definition data for specific definition.
     *
     * @param SuiteInterface      $suite
     * @param DefinitionInterface $definition
     * @param string              $language
     *
     * @return array
     */
    private function getDefinitionData(SuiteInterface $suite, DefinitionInterface $definition, $language)
    {
        $regex = $this->translator->trans($definition->getRegex(), array(), $suite->getId(), $language);

        $regex = preg_replace_callback(
            '/\((?!\?:)[^\)]*\)/',
            function ($capture) {
                return "</comment><definition_capture>{$capture[0]}</definition_capture><comment>";
            },
            $regex
        );

        $suiteName = $suite->getName();
        $type = str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT);
        $description = $definition->getDescription() ? '- ' . $definition->getDescription() . PHP_EOL . "    " : '';
        $path = $definition->getPath();

        return array(
            '{suite}'       => $suiteName,
            '{regex}'       => $regex,
            '{type}'        => $type,
            '{description}' => $description,
            '{path}'        => $path
        );
    }

    /**
     * Creates context pool instance.
     *
     * @param SuiteInterface $suite
     *
     * @return ContextPoolInterface
     *
     * @throws RuntimeException If context pool can not be created
     */
    private function createContextPool(SuiteInterface $suite)
    {
        $contextPoolProvider = new ContextPoolCarrierEvent($suite);

        $this->dispatch(EventInterface::CREATE_CONTEXT_POOL, $contextPoolProvider);
        if (!$contextPoolProvider->hasContextPool()) {
            throw new RuntimeException(sprintf(
                'Can not create context pool for "%s" suite. Is this suite configured properly?',
                $suite->getName()
            ));
        }

        return $contextPoolProvider->getContextPool();
    }

    /**
     * Returns all available definitions for specified suite and context pool.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     *
     * @return DefinitionInterface[]
     */
    private function loadDefinitions(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $definitionsProvider = new DefinitionsCarrierEvent($suite, $contexts);
        $this->dispatch(EventInterface::LOAD_DEFINITIONS, $definitionsProvider);

        return $definitionsProvider->getDefinitions();
    }
}
