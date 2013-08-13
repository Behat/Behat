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
use Behat\Behat\Output\FormatterManager;
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
     * @var FormatterManager
     */
    private $formatterManager;
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
     * @param FormatterManager $formatterManager
     * @param Translator       $translator
     * @param string           $i18nPath
     */
    public function __construct(FormatterManager $formatterManager, Translator $translator, $i18nPath)
    {
        $this->formatterManager = $formatterManager;
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
        $formatters = $this->formatterManager->getFormatters();

        $command
            ->addOption('--format', '-f', InputOption::VALUE_REQUIRED,
                "How to format features. <comment>pretty</comment> is default." . PHP_EOL .
                "Default formatters are:" . PHP_EOL .
                implode(PHP_EOL,
                    array_map(function (FormatterInterface $formatter) {
                        $comment = '- <comment>' . $formatter->getName() . '</comment>: ';
                        $comment .= $formatter->getDescription();

                        return $comment;
                    }, $formatters)
                ) . PHP_EOL .
                "Can use multiple formats at once (splitted with '<comment>,</comment>')"
            )
            ->addOption('--out', null, InputOption::VALUE_REQUIRED,
                "Write formatter output to a file/directory" . PHP_EOL .
                "instead of STDOUT <comment>(output_path)</comment>."
            )
            ->addOption('--lang', null, InputOption::VALUE_REQUIRED,
                'Print formatter output in particular language.'
            )
            ->addOption('--ansi', null, InputOption::VALUE_NONE,
                "Tell behat to to use ANSI color in the output." . PHP_EOL .
                "Behat decides based on your platform and the output" . PHP_EOL .
                "destination if not specified."
            )
            ->addOption('--no-ansi', null, InputOption::VALUE_NONE,
                "Tell behat not to use ANSI color in the output." . PHP_EOL .
                "Behat decides based on your platform and the output" . PHP_EOL .
                "destination if not specified."
            )
            ->addOption('--no-time', null, InputOption::VALUE_NONE,
                "Do not show timer in output."
            )
            ->addOption('--no-paths', null, InputOption::VALUE_NONE,
                "Do not print features, scenarios and step definition paths."
            )
            ->addOption('--no-snippets', null, InputOption::VALUE_NONE,
                "Do not print snippets for undefined steps after suite stats."
            )
            ->addOption('--no-multiline', null, InputOption::VALUE_NONE,
                "Do not print multiline arguments for steps."
            )
            ->addOption('--expand', null, InputOption::VALUE_NONE,
                "Visually expand scenario outline example tables into sub-scenario."
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
        $formats = array();
        if ($input->getOption('format')) {
            $formats = array_map(
                'trim',
                explode(',', $input->getOption('format') ? : 'pretty')
            );
        }

        $this->loadTranslations();
        $this->configureFormatters($formats, $input, $output);
        $this->configureOutput($formats, $input);
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 50;
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
            $this->formatterManager->disableAllFormatters();
            foreach ($formats as $format) {
                $this->formatterManager->enableFormatter($format);
            }
        }

        $this->formatterManager->setFormattersParameterIfExists('decorated', $output->isDecorated());

        if ($input->getOption('verbose')) {
            $this->formatterManager->setFormattersParameterIfExists('verbose', true);
        }
        if ($input->getOption('lang')) {
            $this->formatterManager->setFormattersParameterIfExists('language', $input->getOption('lang'));
        }
        if ($input->getOption('ansi')) {
            $output->setDecorated(true);
            $this->formatterManager->setFormattersParameterIfExists('decorated', true);
        }
        if ($input->getOption('no-ansi')) {
            $output->setDecorated(false);
            $this->formatterManager->setFormattersParameterIfExists('decorated', false);
        }
        if ($input->getOption('no-time')) {
            $this->formatterManager->setFormattersParameterIfExists('time', false);
        }
        if ($input->getOption('no-paths')) {
            $this->formatterManager->setFormattersParameterIfExists('paths', false);
        }
        if ($input->getOption('no-snippets')) {
            $this->formatterManager->setFormattersParameterIfExists('snippets', false);
        }
        if ($input->getOption('no-multiline')) {
            $this->formatterManager->setFormattersParameterIfExists('multiline_arguments', false);
        }
        if ($input->getOption('snippets-paths')) {
            $this->formatterManager->setFormattersParameterIfExists('snippets_paths', true);
        }
        if ($input->getOption('expand')) {
            $this->formatterManager->setFormattersParameterIfExists('expand', true);
        }
    }

    /**
     * Initializes multiple formatters with different outputs.
     *
     * @param array          $formats
     * @param InputInterface $input
     */
    private function configureOutput(array $formats, InputInterface $input)
    {
        if (!$input->getOption('out')) {
            return;
        }

        $outputs = $input->getOption('out');
        $decorated = !$input->getOption('no-ansi');

        if (false === strpos($outputs, ',')) {
            $outputPath = $this->locateOutputPath($outputs);

            $this->formatterManager->setFormattersParameterIfExists('output_path', $outputPath);
            $this->formatterManager->setFormattersParameterIfExists('decorated', $decorated);

            return;
        }

        foreach (array_map('trim', explode(',', $outputs)) as $i => $out) {
            if (!$out || 'null' === $out || 'false' === $out) {
                continue;
            }

            $outputPath = $this->locateOutputPath($out);
            if (isset($formats[$i])) {
                $this->formatterManager->setFormatterParameter($formats[$i], 'output_path', $outputPath);
                $this->formatterManager->setFormatterParameter($formats[$i], 'decorated', $decorated);
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
