<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Translation\Translator;

use Behat\Behat\Console\Output\ConsoleOutput,
    Behat\Behat\Tester\StepTester;

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
     * @var     ParameterBag
     */
    protected $parameters;
    /**
     * Translator.
     *
     * @var     Translator
     */
    protected $translator;
    /**
     * Console output.
     *
     * @var     ConsoleOutput
     */
    private $console;

    /**
     * {@inheritdoc}
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->parameters = new ParameterBag(array_merge(array(
            'stream'        => fopen('php://stdout', 'w'),
            'verbose'       => false,
            'decorated'     => true,
            'time'          => true,
            'language'      => 'en',
            'base_path'     => null,
            'output_path'   => null,
        ), $this->getDefaultParameters()));
    }

    /**
     * Return default parameters to construct ParameterBag.
     *
     * @return  array
     */
    abstract protected function getDefaultParameters();

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        return $this->parameters->get($name);
    }

    /**
     * Return color code by tester result status code.
     *
     * @param   integer $result result status code
     *
     * @return  string          passed|pending|skipped|undefined|failed
     */
    protected function getResultColorCode($result)
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
     * Write message(s) to output console.
     *
     * @param   string|array    $messages   message or array of messages
     * @param   boolean         $newline    do we need to append newline after messages
     */
    protected function write($messages, $newline = false)
    {
        $this->getWritingConsole()->write($messages, $newline);
    }

    /**
     * Write newlined message(s) to output console.
     *
     * @param   string|array    $messages   message or array of messages
     */
    protected function writeln($messages = '')
    {
        $this->write($messages, true);
    }

    /**
     * Return console instance, prepared to write.
     *
     * @return  ConsoleOutput
     */
    protected function getWritingConsole()
    {
        if (null === $this->console || $this->console->getStream() !== $this->parameters->get('stream')) {
            $this->console = new ConsoleOutput($this->parameters->get('stream'));
        }

        $this->console->setVerbosity($this->parameters->get('verbose') ? 2 : 1);
        $this->console->setDecorated($this->parameters->get('decorated'));

        return $this->console;
    }

    /**
     * Translate message to default formatter language.
     *
     * @param   string  $message        message to translate
     * @param   array   $parameters     message parameters
     *
     * @return  string
     */
    protected function translate($message, array $parameters = array())
    {
        return $this->translator->trans(
            $message, $parameters, 'behat', $this->parameters->get('language')
        );
    }

    /**
     * Translate number choicer to default formatter language.
     *
     * @param   string  $message        message specification to translate
     * @param   string  $number         choice number
     * @param   array   $parameters     message parameters
     *
     * @return  string
     */
    protected function translateChoice($message, $number, array $parameters = array())
    {
        return $this->translator->transChoice(
            $message, $number, $parameters, 'behat', $this->parameters->get('language')
        );
    }
}
