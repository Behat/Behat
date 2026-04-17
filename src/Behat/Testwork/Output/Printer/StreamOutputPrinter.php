<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Printer\Factory\OutputFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StreamOutputPrinter implements OutputPrinter
{
    private ?OutputInterface $output = null;

    public function __construct(
        private readonly OutputFactory $outputFactory,
    ) {
    }

    protected function getOutputFactory(): OutputFactory
    {
        return $this->outputFactory;
    }

    public function setOutputPath(string $path): void
    {
        $this->outputFactory->setOutputPath($path);
        $this->flush();
    }

    public function setOutputStyles(array $styles): void
    {
        $this->outputFactory->setOutputStyles($styles);
        $this->flush();
    }

    public function setOutputDecorated(bool $decorated): void
    {
        $this->outputFactory->setOutputDecorated($decorated);
        $this->flush();
    }

    public function setOutputVerbosity(int $level): void
    {
        $this->outputFactory->setOutputVerbosity($level);
        $this->flush();
    }

    public function write(string|array $messages): void
    {
        $this->getWritingStream()->write($messages, false);
    }

    public function writeln(string|array $messages = ''): void
    {
        $this->getWritingStream()->write($messages, true);
    }

    public function flush(): void
    {
        $this->output = null;
    }

    /**
     * Returns output instance, prepared to write.
     *
     * @return OutputInterface
     */
    final protected function getWritingStream()
    {
        if (!$this->output instanceof OutputInterface) {
            $this->output = $this->outputFactory->createOutput();
        }

        return $this->output;
    }
}
