<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Exception;

/**
 * Testwork abstract CLI formatter.
 *
 * Provides easy API to write output to both console and filesystem.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class CliFormatter implements Formatter
{
    /**
     * @var OutputPrinter
     */
    private $printer;
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;
    /**
     * @var array
     */
    private $parameters = array();

    /**
     * Initializes formatter.
     *
     * @param OutputPrinter      $printer
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(OutputPrinter $printer, ExceptionPresenter $exceptionPresenter)
    {
        $this->printer = $printer;
        $this->exceptionPresenter = $exceptionPresenter;
        $this->parameters = array();
    }

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    final public function setParameter($name, $value)
    {
        switch ($name) {
            case 'output_verbosity':
                $this->printer->setOutputVerbosity($value);
                break;
            case 'output_path':
                $this->printer->setOutputPath($value);
                break;
            case 'output_decorate':
                $this->printer->setOutputDecorated($value);
                break;
            case 'output_styles':
                $this->printer->setOutputStyles($value);
                break;
        }

        $this->parameters[$name] = $value;
    }

    /**
     * Returns parameter value.
     *
     * @param string $name
     *
     * @return mixed
     */
    final public function getParameter($name)
    {
        switch ($name) {
            case 'output_verbosity':
                return $this->printer->getOutputVerbosity();
            case 'output_path':
                return $this->printer->getOutputPath();
            case 'output_decorate':
                return $this->printer->isOutputDecorated();
            case 'output_styles':
                return $this->printer->getOutputStyles();
        }

        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * Writes message(s) to output console.
     *
     * @param string|array $messages message or array of messages
     */
    final public function write($messages)
    {
        $this->printer->write($messages);
    }

    /**
     * Writes newlined message(s) to output console.
     *
     * @param string|array $messages message or array of messages
     */
    final public function writeln($messages = '')
    {
        $this->printer->writeln($messages);
    }

    /**
     * Presents exception as a string.
     *
     * @param Exception $exception
     *
     * @return string
     */
    final public function presentException(Exception $exception)
    {
        return $this->exceptionPresenter->presentException($exception, $this->printer->getOutputVerbosity());
    }
}
