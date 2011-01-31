<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Translation\Translator;

use Behat\Behat\Console\Output\ConsoleOutput,
    Behat\Behat\Tester\StepTester;

abstract class ConsoleFormatter implements FormatterInterface
{
    protected   $parameters;
    protected   $translator;
    private     $console;

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

    abstract protected function getDefaultParameters();

    public function setParameter($name, $value)
    {
        $this->parameters->set($name, $value);
    }

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

    protected function write($messages, $newline = false)
    {
        $this->getWritingConsole()->write($messages, $newline);
    }

    protected function writeln($messages = '')
    {
        $this->write($messages, true);
    }

    protected function getWritingConsole()
    {
        if (null === $this->console || $this->console->getStream() !== $this->parameters->get('stream')) {
            $this->console = new ConsoleOutput($this->parameters->get('stream'));
        }

        $this->console->setVerbosity($this->parameters->get('verbose') ? 2 : 1);
        $this->console->setDecorated($this->parameters->get('decorated'));

        return $this->console;
    }

    protected function translate($message, array $parameters = array())
    {
        return $this->translator->trans(
            $message, $parameters, 'messages', $this->parameters->get('language')
        );
    }

    protected function translateChoice($message, $number, array $parameters = array())
    {
        return $this->translator->transChoice(
            $message, $number, $parameters, 'messages', $this->parameters->get('language')
        );
    }
}
