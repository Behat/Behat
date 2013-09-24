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
     * @param string           $type
     */
    public function printDefinitions(OutputInterface $output, array $suites, $language = 'en', $type = 'i')
    {
        $output->getFormatter()->setStyle('regex', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('regex_capture', new OutputFormatterStyle('yellow', null, array('bold')));
        $output->getFormatter()->setStyle('dimmed', new OutputFormatterStyle('black', null, array('bold')));

        if ('i' === $type) {
            $output->writeln($this->getDefinitionsInfo($suites, null, $language) . PHP_EOL);
        } elseif ('l' === $type) {
            $output->writeln($this->getDefinitionsList($suites, $language) . PHP_EOL);
        } else {
            $output->writeln($this->getDefinitionsInfo($suites, $type, $language) . PHP_EOL);
        }
    }

    /**
     * Shows definitions as simple list.
     *
     * @param SuiteInterface[] $suites
     * @param string           $language
     *
     * @return string
     */
    protected function getDefinitionsList(array $suites, $language = 'en')
    {
        $output = array();
        foreach ($suites as $suite) {
            $contextPool = $this->createContextPool($suite);
            $definitions = $this->loadDefinitions($suite, $contextPool);

            foreach ($definitions as $definition) {
                $output[] = strtr('{suite} <dimmed>|</dimmed> <info>{type}</info> <regex>{regex}</regex>', array(
                    '{suite}' => $suite->getName(),
                    '{type}'  => str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT),
                    '{regex}' => $this->getDefinitionRegexString($suite, $definition, $language),
                ));
            }
        }

        return rtrim(implode(PHP_EOL, $output));
    }

    /**
     * Shows definitions as list with descriptions and paths.
     *
     * @param SuiteInterface[] $suites
     * @param string           $search
     * @param string           $language
     *
     * @return string
     */
    protected function getDefinitionsInfo(array $suites, $search = null, $language = 'en')
    {
        $output = array();
        foreach ($suites as $suite) {
            $contextPool = $this->createContextPool($suite);
            $definitions = $this->loadDefinitions($suite, $contextPool);

            foreach ($definitions as $definition) {
                $lines = array();
                $regex = $this->getDefinitionRegex($suite, $definition, $language);

                if (null !== $search && false === mb_strpos($regex, $search, 0, 'utf8') && !preg_match($regex, $search)) {
                    continue;
                }

                $lines[] = strtr('{suite} <dimmed>|</dimmed> <info>{type}</info> <regex>{regex}</regex>', array(
                    '{suite}' => $suite->getName(),
                    '{type}'  => $definition->getType(),
                    '{regex}' => $this->getDefinitionRegexString($suite, $definition, $language),
                ));

                if ($definition->getDescription()) {
                    $lines[] = strtr('{space}<dimmed>|</dimmed> {description}', array(
                        '{space}'       => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                        '{description}' => $definition->getDescription()
                    ));
                }

                $lines[] = strtr('{space}<dimmed>|</dimmed> at `{path}`', array(
                    '{space}' => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                    '{path}'  => $definition->getPath()
                ));

                $output[] = implode(PHP_EOL, $lines) . PHP_EOL;
            }
        }

        return rtrim(implode(PHP_EOL, $output));
    }

    /**
     * Prepares definition regex for printing.
     *
     * @param SuiteInterface      $suite
     * @param DefinitionInterface $definition
     * @param string              $language
     *
     * @return string
     */
    private function getDefinitionRegexString(
        SuiteInterface $suite,
        DefinitionInterface $definition,
        $language
    )
    {
        return preg_replace_callback(
            '/\((?!\?:)[^\)]*\)/',
            function ($capture) {
                return "</regex><regex_capture>{$capture[0]}</regex_capture><regex>";
            },
            $this->getDefinitionRegex($suite, $definition, $language)
        );
    }

    /**
     * Returns definition regex translated into provided language.
     *
     * @param SuiteInterface      $suite
     * @param DefinitionInterface $definition
     * @param string              $language
     *
     * @return string
     */
    private function getDefinitionRegex(SuiteInterface $suite, DefinitionInterface $definition, $language)
    {
        return $this->translator->trans($definition->getRegex(), array(), $suite->getId(), $language);
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
