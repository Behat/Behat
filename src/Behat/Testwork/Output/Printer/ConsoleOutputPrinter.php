<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Exception\BadOutputPathException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Symfony2\Console-based output printer for printing to streams.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleOutputPrinter implements OutputPrinter
{
    /**
     * @var null|string
     */
    private $outputPath;
    /**
     * @var array
     */
    private $outputStyles = array();
    /**
     * @var null|Boolean
     */
    private $outputDecorated = null;
    /**
     * @var integer
     */
    private $verbosityLevel = 0;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function setOutputPath($path)
    {
        $this->outputPath = $path;
        $this->flush();
    }

    /**
     * Returns output path.
     *
     * @return null|string
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * Sets output styles.
     *
     * @param array $styles
     */
    public function setOutputStyles(array $styles)
    {
        $this->outputStyles = $styles;
        $this->flush();
    }

    /**
     * Returns output styles.
     *
     * @return array
     */
    public function getOutputStyles()
    {
        return $this->outputStyles;
    }

    /**
     * Forces output to be decorated.
     *
     * @param Boolean $decorated
     */
    public function setOutputDecorated($decorated)
    {
        $this->outputDecorated = $decorated;
        $this->flush();
    }

    /**
     * Returns output decoration status.
     *
     * @return null|Boolean
     */
    public function isOutputDecorated()
    {
        return $this->outputDecorated;
    }

    /**
     * Sets output verbosity level.
     *
     * @param integer $level
     */
    public function setOutputVerbosity($level)
    {
        $this->verbosityLevel = intval($level);
        $this->flush();
    }

    /**
     * Returns output verbosity level.
     *
     * @return integer
     */
    public function getOutputVerbosity()
    {
        return $this->verbosityLevel;
    }

    /**
     * Writes message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function write($messages)
    {
        $this->getWritingStream()->write($messages, false);
    }

    /**
     * Writes newlined message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln($messages = '')
    {
        $this->getWritingStream()->write($messages, true);
    }

    /**
     * Clear output stream, so on next write formatter will need to init (create) it again.
     */
    public function flush()
    {
        $this->output = null;
    }

    /**
     * Creates output formatter that is used to create a stream.
     *
     * @return OutputFormatter
     */
    protected function createOutputFormatter()
    {
        return new OutputFormatter();
    }

    /**
     * Configure output stream parameters.
     *
     * @param OutputInterface $output
     */
    protected function configureOutputStream(OutputInterface $output)
    {
        $verbosity = $this->verbosityLevel ? OutputInterface::VERBOSITY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $output->setVerbosity($verbosity);

        if (null !== $this->outputDecorated) {
            $output->getFormatter()->setDecorated($this->outputDecorated);
        }
    }

    /**
     * Returns new output stream.
     *
     * Override this method & call flush() to write output in another stream
     *
     * @return resource
     *
     * @throws BadOutputPathException
     */
    protected function createOutputStream()
    {
        if (null === $this->outputPath) {
            $stream = fopen('php://stdout', 'w');
        } elseif (!is_dir($this->outputPath)) {
            $stream = fopen($this->outputPath, 'w');
        } else {
            throw new BadOutputPathException(sprintf(
                'Filename expected as `output_path` parameter, but got `%s`.',
                $this->outputPath
            ), $this->outputPath);
        }

        return $stream;
    }

    /**
     * Returns new output stream.
     *
     * @param null|resource $stream
     *
     * @return StreamOutput
     *
     * @uses createOutputStream()
     */
    protected function createOutput($stream = null)
    {
        $stream = $stream ? : $this->createOutputStream();
        $format = $this->createOutputFormatter();

        // set user-defined styles
        foreach ($this->outputStyles as $name => $options) {
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

        $output = new StreamOutput(
            $stream,
            StreamOutput::VERBOSITY_NORMAL,
            $this->outputDecorated,
            $format
        );
        $this->configureOutputStream($output);

        return $output;
    }

    /**
     * Returns output instance, prepared to write.
     *
     * @return StreamOutput
     */
    final protected function getWritingStream()
    {
        if (null === $this->output) {
            $this->output = $this->createOutput();
        }

        return $this->output;
    }
}
