<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Translation\Translator,
    Symfony\Component\Console\Output\StreamOutput,
    Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Behat\Behat\Event\StepEvent,
    Behat\Behat\Exception\FormatterException,
    Behat\Behat\Console\Formatter\OutputFormatter;

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
     * @var     Symfony\Component\Console\Output\StreamOutput
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
            'support_path'          => null,
            'output'                => null,
            'output_path'           => null,
            'output_styles'         => array(),
            'output_decorate'       => null,
            'snippets'              => true,
            'snippets_paths'        => false,
            'paths'                 => true,
            'expand'                => false,
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
        $this->parameters->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    final public function getParameter($name)
    {
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
            case StepEvent::PASSED:     return 'passed';
            case StepEvent::SKIPPED:    return 'skipped';
            case StepEvent::PENDING:    return 'pending';
            case StepEvent::UNDEFINED:  return 'undefined';
            case StepEvent::FAILED:     return 'failed';
            case StepEvent::UNSTABLE:   return 'unstable';
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
     * @return  Symfony\Component\Console\Output\StreamOutput
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
        if (is_resource($stream = $this->parameters->get('output'))) {
            return $stream;
        }

        $outputPath = $this->parameters->get('output_path');

        if (null === $outputPath) {
            $stream = fopen('php://stdout', 'w');
        } elseif (!is_dir($outputPath)) {
            $stream = fopen($outputPath, 'w');
        } else {
            throw new FormatterException(sprintf(
                'Filename expected as "output_path" parameter of "%s" formatter, but got: "%s"',
                basename(str_replace('\\', '/', get_class($this))), $outputPath
            ));
        }

        return $stream;
    }

    /**
     * Returns new output console.
     *
     * @return  Symfony\Component\Console\Output\StreamOutput
     *
     * @uses    createOutputStream()
     */
    protected function createOutputConsole()
    {
        $stream = $this->createOutputStream();
        $format = new OutputFormatter();

        // set user-defined styles
        foreach ($this->parameters->get('output_styles') as $name => $options) {
            $style = new OutputFormatterStyle();

            if (isset($options[0])) {
                $style->setForeground($options[0]);
            }
            if (isset($options[1])) {
                $style->setBackground($options[1]);
            }
            if (isset($options[2])) {
                $style->setOptions($options[2]);
            }

            $format->setStyle($name, $style);
        }

        return new StreamOutput(
            $stream, StreamOutput::VERBOSITY_NORMAL, $this->parameters->get('output_decorate'), $format
        );
    }

    /**
     * Configure output console parameters.
     *
     * @param   Symfony\Component\Console\Output\StreamOutput    $console
     */
    protected function configureOutputConsole(StreamOutput $console)
    {
        $console->setVerbosity(
            $this->parameters->get('verbose') ? StreamOutput::VERBOSITY_VERBOSE : StreamOutput::VERBOSITY_NORMAL
        );
        $console->getFormatter()->setDecorated(
            $this->parameters->get('decorated')
        );
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
