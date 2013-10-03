<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Output\Formatter\FormatterInterface;
use Behat\Behat\Output\OutputManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;

/**
 * Format processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatProcessor implements ProcessorInterface
{
    /**
     * @var OutputManager
     */
    private $outputManager;
    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var string
     */
    private $i18nPath;

    /**
     * Initializes processor.
     *
     * @param OutputManager $outputManager
     * @param Translator    $translator
     * @param string        $i18nPath
     */
    public function __construct(OutputManager $outputManager, Translator $translator, $i18nPath)
    {
        $this->outputManager = $outputManager;
        $this->translator = $translator;
        $this->i18nPath = $i18nPath;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $formatters = $this->outputManager->getFormatters();

        $command
            ->addOption('--format', '-f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                "How to format features. <comment>pretty</comment> is default. Available" . PHP_EOL .
                "formats are:" . PHP_EOL .
                implode(PHP_EOL,
                    array_map(function (FormatterInterface $formatter) {
                        $comment = '- <comment>' . $formatter->getName() . '</comment>: ';
                        $comment .= $formatter->getDescription();

                        return $comment;
                    }, $formatters)
                ) . PHP_EOL .
                "You can use multiple formats at the same time."
            )
            ->addOption('--out', '-o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                "Write format output to a file/directory instead of" . PHP_EOL .
                "STDOUT <comment>(output_path)</comment>. You can also provide different" . PHP_EOL .
                "outputs to multiple formats."
            )
            ->addOption('--lang', null, InputOption::VALUE_REQUIRED,
                'Print formatter output in particular language.'
            )
            ->addOption('--ansi', null, InputOption::VALUE_NONE,
                "Tell behat to to use ANSI color in the output. Behat" . PHP_EOL .
                "decides based on your platform and the output if not" . PHP_EOL .
                "specified."
            )
            ->addOption('--no-ansi', null, InputOption::VALUE_NONE,
                "Tell behat not to use ANSI color in the output."
            )
            ->addOption('--no-time', null, InputOption::VALUE_NONE,
                "Do not show timer in the output."
            )
            ->addOption('--no-paths', null, InputOption::VALUE_NONE,
                "Do not print features, scenarios and step definition" . PHP_EOL .
                "paths."
            )
            ->addOption('--no-snippets', null, InputOption::VALUE_NONE,
                "Do not print snippets for undefined steps after stats."
            )
            ->addOption('--no-multiline', null, InputOption::VALUE_NONE,
                "Do not print multiline arguments for steps."
            )
            ->addOption('--expand', null, InputOption::VALUE_NONE,
                "Visually expand scenario outline examples into sub-" . PHP_EOL .
                "scenarios."
            )
            ->addOption('--snippets-paths', null, InputOption::VALUE_NONE,
                "Print details about undefined steps in their snippets."
            );
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $formats = $input->getOption('format');
        $outputs = $input->getOption('out');

        $this->loadTranslations();
        $this->configureFormatters($formats, $input, $output);
        $this->configureOutputs($formats, $outputs, !$input->getOption('no-ansi'));
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 40;
    }

    /**
     * Loads default formatter translations from filesystem.
     */
    private function loadTranslations()
    {
        foreach (require($this->i18nPath) as $lang => $messages) {
            $this->translator->addResource('array', $messages, $lang, 'formatter');
        }
    }

    /**
     * Configures formatters based on container, input and output configurations.
     *
     * @param array           $formats
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function configureFormatters(array $formats, InputInterface $input, OutputInterface $output)
    {
        if (count($formats)) {
            $this->outputManager->disableAllFormatters();
            foreach ($formats as $format) {
                $this->outputManager->enableFormatter($format);
            }
        }

        $this->outputManager->setFormattersParameterIfExists('decorated', $output->isDecorated());

        if ($input->getOption('verbose')) {
            $this->outputManager->setFormattersParameterIfExists('verbose', true);
        }
        if ($input->getOption('lang')) {
            $this->outputManager->setFormattersParameterIfExists('language', $input->getOption('lang'));
        }
        if ($input->getOption('ansi')) {
            $output->setDecorated(true);
            $this->outputManager->setFormattersParameterIfExists('decorated', true);
        }
        if ($input->getOption('no-ansi')) {
            $output->setDecorated(false);
            $this->outputManager->setFormattersParameterIfExists('decorated', false);
        }
        if ($input->getOption('no-time')) {
            $this->outputManager->setFormattersParameterIfExists('time', false);
        }
        if ($input->getOption('no-paths')) {
            $this->outputManager->setFormattersParameterIfExists('paths', false);
        }
        if ($input->getOption('no-snippets')) {
            $this->outputManager->setFormattersParameterIfExists('snippets', false);
        }
        if ($input->getOption('no-multiline')) {
            $this->outputManager->setFormattersParameterIfExists('multiline_arguments', false);
        }
        if ($input->getOption('snippets-paths')) {
            $this->outputManager->setFormattersParameterIfExists('snippets_paths', true);
        }
        if ($input->getOption('expand')) {
            $this->outputManager->setFormattersParameterIfExists('expand', true);
        }
    }

    /**
     * Initializes multiple formatters with different outputs.
     *
     * @param array   $formats
     * @param array   $outputs
     * @param Boolean $decorated
     */
    private function configureOutputs(array $formats, array $outputs, $decorated = false)
    {
        if (!count($outputs)) {
            return;
        }

        if (1 == count($outputs) && 'std' !== $outputs[0] && 'null' !== $outputs[0] && 'false' !== $outputs[0]) {
            $outputPath = $this->locateOutputPath($outputs[0]);

            $this->outputManager->setFormattersParameterIfExists('output_path', $outputPath);
            $this->outputManager->setFormattersParameterIfExists('decorated', $decorated);

            return;
        }

        foreach ($outputs as $i => $out) {
            if ('std' === $out || 'null' === $out || 'false' === $out) {
                continue;
            }

            $outputPath = $this->locateOutputPath($out);
            if (isset($formats[$i])) {
                $this->outputManager->setFormatterParameter($formats[$i], 'output_path', $outputPath);
                $this->outputManager->setFormatterParameter($formats[$i], 'decorated', $decorated);
            }
        }
    }

    /**
     * Locates output path from relative one.
     *
     * @param string $out
     *
     * @return string
     */
    private function locateOutputPath($out)
    {
        if ($this->isAbsolutePath($out)) {
            return $out;
        }

        $out = getcwd() . DIRECTORY_SEPARATOR . $out;

        if (!file_exists($out)) {
            touch($out);
            $out = realpath($out);
            unlink($out);
        } else {
            $out = realpath($out);
        }

        return $out;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Boolean
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
