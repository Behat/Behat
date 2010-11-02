<?php

namespace Everzet\Behat\Output;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Output\Formatter\FormatterInterface;
use Everzet\Behat\Output\Formatter\TranslatableFormatterInterface;
use Everzet\Behat\Output\Formatter\ColorableFormatterInterface;
use Everzet\Behat\Output\Formatter\TimableFormatterInterface;
use Everzet\Behat\Output\Formatter\VerbosableFormatterInterface;
use Everzet\Behat\Output\Formatter\ContainerAwareFormatterInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Output Manager.
 * Manages Formatters & Prints Output.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputManager
{
    protected $container;

    protected $output;
    protected $colors       = true;
    protected $timer        = true;
    protected $verbose      = false;
    protected $locale       = 'en';
    protected $outputPath   = null;

    protected $isFormatterRegistered    = false;
    protected $formatters               = array();
    protected $formatter;

    /**
     * Create container.
     *
     * @param   Container       $container  container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set output path for the formatters. 
     * 
     * @param   string  $path   output file/folder path
     */
    public function setOutputPath($path)
    {
        $this->outputPath = $path;

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Set output instance. 
     * 
     * @param   OutputInterface $output output instance
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Allow colors in output. 
     * 
     * @param   boolean $colors     allow colors in output
     */
    public function showColors($colors = true)
    {
        $this->colors = (bool) $colors;
    }

    /**
     * Show timer in output. 
     * 
     * @param   boolean $timer      show timer in output
     */
    public function showTimer($timer = true)
    {
        $this->timer = (bool) $timer;
    }

    /**
     * Set output to be verbose. 
     * 
     * @param   boolean $verbose    is output verbose
     */
    public function beVerbose($verbose = true)
    {
        $this->verbose = (bool) $verbose;
    }

    /**
     * Set output locale (translation). 
     * 
     * @param   string  $locale     output locale ('en' or 'ru' etc.)
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Add a formatter.
     *
     * @param   string              $name       the formatter name
     * @param   FormatterInterface  $formatter  formatter instance
     */
    public function addFormatter($name, FormatterInterface $formatter)
    {
        $this->formatters[$name] = $formatter;
    }

    /**
     * Set current formatter.
     * 
     * @param   string  $name   formatter name
     */
    public function setFormatter($name)
    {
        if (!isset($this->formatters[$name])) {
            throw new \RuntimeException(sprintf('The "%s" formatter is not registered.', $name));
        }

        $this->formatter = $name;
    }

    /**
     * Register output manager event listeners. 
     * 
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('behat.output.write', array($this, 'write'));

        $formatter = $this->formatters[$this->formatter];
        $formatter->registerListeners($dispatcher);

        if ($formatter instanceof TimableFormatterInterface) {
            $formatter->showTimer($this->timer);
        }
        if ($formatter instanceof ContainerAwareFormatterInterface) {
            $formatter->setContainer($this->container);
        }
        if ($formatter instanceof TranslatableFormatterInterface) {
            $translator = $this->container->get('gherkin.translator');
            $translator->setLocale($this->locale);

            $formatter->setTranslator($translator);
        }
        if ($formatter instanceof ColorableFormatterInterface) {
            $formatter->showColors($this->hasColorSupport());
        }
        if ($formatter instanceof VerbosableFormatterInterface) {
            $formatter->beVerbose($this->verbose);
        }
    }

    /**
     * Print string to console on `behat.output.write` event.
     *
     * @param   Event   $event  event
     */
    public function write(Event $event)
    {
        $ending = $event->getParameter('newline') ? "\n" : '';

        if (!empty($this->outputPath)) {
            if ($event->hasParameter('file')) {
                if (!is_dir($dir = $this->outputPath)) {
                    throw new \InvalidArgumentException(sprintf('Directory path expected as --out, but %s given', $dir));
                }

                file_put_contents($dir . '/'. $event->getParameter('file'), $event->getParameter('string') . $ending, \FILE_APPEND);
            } else {
                file_put_contents($this->outputPath, $event->getParameter('string') . $ending, \FILE_APPEND);
            }
        } else {
            if ($event->hasParameter('file')) {
                throw new \InvalidArgumentException(sprintf('You *must* specify --out DIR for the %s formatter', $this->formatter));
            }

            $this->output->write($event->getParameter('string'), $event->getParameter('newline'), 1);
        }
    }

    /**
     * Returns true if the stream supports colorization.
     *
     * Colorization is disabled if not supported by the stream:
     *
     * - windows without ansicon
     * - non tty consoles
     *
     * @return  boolean             true if the stream supports colorization, false otherwise
     */
    protected function hasColorSupport()
    {
        if ($this->colors) {
            if ('\\' == DIRECTORY_SEPARATOR) {
                return false !== getenv('ANSICON');
            } else {
                return function_exists('posix_isatty') && @posix_isatty($this->output->getStream());
            }
        } else {
            return false;
        }
    }
}

