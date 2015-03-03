<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Factory;

use Behat\Testwork\Output\Exception\BadOutputPathException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Creates an output stream for the console.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleOutputFactory extends OutputFactory
{
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
        $verbosity = $this->getOutputVerbosity() ? OutputInterface::VERBOSITY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $output->setVerbosity($verbosity);

        if (null !== $this->isOutputDecorated()) {
            $output->getFormatter()->setDecorated($this->isOutputDecorated());
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
        if (null === $this->getOutputPath()) {
            $stream = fopen('php://stdout', 'w');
        } elseif (!is_dir($this->getOutputPath())) {
            $stream = fopen($this->getOutputPath(), 'w');
        } else {
            throw new BadOutputPathException(sprintf(
                'Filename expected as `output_path` parameter, but got `%s`.',
                $this->getOutputPath()
            ), $this->getOutputPath());
        }

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function createOutput($stream = null)
    {
        $stream = $stream ? : $this->createOutputStream();
        $format = $this->createOutputFormatter();

        // set user-defined styles
        foreach ($this->getOutputStyles() as $name => $options) {
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
            $this->isOutputDecorated(),
            $format
        );
        $this->configureOutputStream($output);

        return $output;
    }
}
