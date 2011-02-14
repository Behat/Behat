<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Translation\Translator;

use Behat\Behat\Console\Output\ConsoleOutput,
    Behat\Behat\Tester\StepTester,
    Behat\Behat\Exception\FormatterException;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ConsoleFormatter implements FormatterInterface
{
    /**
     * Formatter parameters.
     *
     * @var     Symfony\Component\DependencyInjection\ParameterBag\ParameterBag
     */
    protected $parameters;
    /**
     * Translator.
     *
     * @var     Symfony\Component\Translation\Translator
     */
    private $translator;
    /**
     * Console output.
     *
     * @var     Behat\Behat\Console\Output\ConsoleOutput
     */
    private $console;

    /**
     * Initialize formatter.
     *
     * @uses    getDefaultParameters()
     */
    public function __construct()
    {
        $this->parameters = new ParameterBag(array_merge(array(
            'verbose'               => false,
            'decorated'             => true,
            'time'                  => true,
            'language'              => 'en',
            'base_path'             => null,
            'output_path'           => null,
            'multiline_arguments'   => true,
        ), $this->getDefaultParameters()));
    }

    /**
     * {@inheritdoc}
     */
    final public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return  array
     */
    abstract protected function getDefaultParameters();

    /**
     * {@inheritdoc}
     */
    final public function hasParameter($name)
    {
        return $this->parameters->has($name);
    }

    /**
     * {@inheritdoc}
     */
    final public function setParameter($name, $value)
    {
        if (!$this->hasParameter($name)) {
            throw new FormatterException(
                sprintf('The %s doesn\'t support "%s" parameter', get_class($this), $name)
            );
        }

        $this->parameters->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    final public function getParameter($name)
    {
        if (!$this->hasParameter($name)) {
            throw new FormatterException(
                sprintf('The %s doesn\'t support "%s" parameter', get_class($this), $name)
            );
        }

        return $this->parameters->get($name);
    }

    /**
     * Returns color code from tester result status code.
     *
     * @param   integer $result tester result status code
     *
     * @return  string          passed|pending|skipped|undefined|failed
     */
    final protected function getResultColorCode($result)
    {
        switch ($result) {
            case StepTester::PASSED:
                return 'passed';
            case StepTester::SKIPPED:
                return 'skipped';
            case StepTester::PENDING:
                return 'pending';
            case StepTester::UNDEFINED:
                return 'undefined';
            case StepTester::FAILED:
                return 'failed';
        }
    }

    /**
     * Writes message(s) to output console.
     *
     * @param   string|array    $messages   message or array of messages
     * @param   boolean         $newline    do we need to append newline after messages
     *
     * @uses    getWritingConsole()
     */
    final protected function write($messages, $newline = false)
    {
        $this->getWritingConsole()->write($messages, $newline);
    }

    /**
     * Writes newlined message(s) to output console.
     *
     * @param   string|array    $messages   message or array of messages
     */
    final protected function writeln($messages = '')
    {
        $this->write($messages, true);
    }

    /**
     * Returns console instance, prepared to write.
     *
     * @return  Behat\Behat\Console\Output\ConsoleOutput
     *
     * @uses    createOutputConsole()
     * @uses    configureOutputConsole()
     */
    final protected function getWritingConsole()
    {
        if (null === $this->console) {
            $this->console = $this->createOutputConsole();
        }
        $this->configureOutputConsole($this->console);

        return $this->console;
    }

    /**
     * Returns new output stream for console.
     *
     * Override this method & call flushOutputConsole() to write output in another stream
     *
     * @return  resource
     */
    protected function createOutputStream()
    {
        $outputPath = $this->parameters->get('output_path');

        if (null === $outputPath) {
            $stream = fopen('php://stdout', 'w');
        } elseif (!is_dir($outputPath)) {
            $stream = fopen($outputPath, 'w');
        } else {
            throw new FormatterException(sprintf(
                'Filename expected as "output_path" parameter of %s, but got: %s',
                get_class($this),
                $outputPath
            ));
        }

        return $stream;
    }

    /**
     * Returns new output console.
     *
     * @return  Behat\Behat\Console\Output\ConsoleOutput
     *
     * @uses    createOutputStream()
     */
    protected function createOutputConsole()
    {
        $stream = $this->createOutputStream();

        return new ConsoleOutput($stream);
    }

    /**
     * Configure output console parameters.
     *
     * @param   Behat\Behat\Console\Output\ConsoleOutput    $console
     */
    protected function configureOutputConsole(ConsoleOutput $console)
    {
        $console->setVerbosity($this->parameters->get('verbose') ? 2 : 1);
        $console->setDecorated($this->parameters->get('decorated'));
    }

    /**
     * Clear output console, so on next write formatter will need to init (create) it again.
     *
     * @see     createOutputConsole()
     */
    final protected function flushOutputConsole()
    {
        $this->console = null;
    }

    /**
     * Translates message to output language.
     *
     * @param   string  $message        message to translate
     * @param   array   $parameters     message parameters
     *
     * @return  string
     */
    final protected function translate($message, array $parameters = array())
    {
        return $this->translator->trans(
            $message, $parameters, 'behat', $this->parameters->get('language')
        );
    }

    /**
     * Translates numbered message to output language.
     *
     * @param   string  $message        message specification to translate
     * @param   string  $number         choice number
     * @param   array   $parameters     message parameters
     *
     * @return  string
     */
    final protected function translateChoice($message, $number, array $parameters = array())
    {
        return $this->translator->transChoice(
            $message, $number, $parameters, 'behat', $this->parameters->get('language')
        );
    }
}
