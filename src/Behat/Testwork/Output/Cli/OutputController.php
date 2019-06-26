<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\OutputManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures formatters based on user input.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputController implements Controller
{
    /**
     * @var OutputManager
     */
    private $manager;

    /**
     * Initializes controller.
     *
     * @param OutputManager $manager
     */
    public function __construct(OutputManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--format', '-f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'How to format tests output. <comment>pretty</comment> is default.' . PHP_EOL .
                'Available formats are:' . PHP_EOL . $this->getFormatterDescriptions() .
                'You can use multiple formats at the same time.'
            )
            ->addOption(
                '--out', '-o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Write format output to a file/directory instead of' . PHP_EOL .
                'STDOUT <comment>(output_path)</comment>. You can also provide different' . PHP_EOL .
                'outputs to multiple formats.'
            )
            ->addOption(
                '--format-settings', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Set formatters parameters using json object.' . PHP_EOL .
                'Keys are parameter names, values are values.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formats = $input->getOption('format');
        $outputs = $input->getOption('out');

        $this->configureFormatters($formats, $input, $output);
        $this->configureOutputs($formats, $outputs, $output->isDecorated());
    }

    /**
     * Configures formatters based on container, input and output configurations.
     *
     * @param array           $formats
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function configureFormatters(array $formats, InputInterface $input, OutputInterface $output)
    {
        $this->enableFormatters($formats);
        $this->setFormattersParameters($input, $output);
    }

    /**
     * Enables formatters.
     *
     * @param array $formats
     */
    protected function enableFormatters(array $formats)
    {
        if (count($formats)) {
            $this->manager->disableAllFormatters();
            foreach ($formats as $format) {
                $this->manager->enableFormatter($format);
            }
        }
    }

    /**
     * Sets formatters parameters based on input & output.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function setFormattersParameters(InputInterface $input, OutputInterface $output)
    {
        $this->manager->setFormattersParameter('output_decorate', $output->isDecorated());

        if ($input->getOption('format-settings')) {
            foreach ($input->getOption('format-settings') as $jsonSettings) {
                $this->loadJsonSettings($jsonSettings);
            }
        }
    }

    /**
     * Locates output path from relative one.
     *
     * @param string $path
     *
     * @return string
     */
    protected function locateOutputPath($path)
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $path = getcwd() . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($path)) {
            touch($path);
            $path = realpath($path);
            unlink($path);
        } else {
            $path = realpath($path);
        }

        return $path;
    }

    /**
     * Initializes multiple formatters with different outputs.
     *
     * @param array   $formats
     * @param array   $outputs
     * @param bool $decorated
     */
    private function configureOutputs(array $formats, array $outputs, $decorated = false)
    {
        if (1 == count($outputs) && !$this->isStandardOutput($outputs[0])) {
            $outputPath = $this->locateOutputPath($outputs[0]);

            $this->manager->setFormattersParameter('output_path', $outputPath);
            $this->manager->setFormattersParameter('output_decorate', $decorated);

            return;
        }

        foreach ($outputs as $i => $out) {
            if ($this->isStandardOutput($out)) {
                continue;
            }

            $outputPath = $this->locateOutputPath($out);
            if (isset($formats[$i])) {
                $this->manager->setFormatterParameter($formats[$i], 'output_path', $outputPath);
                $this->manager->setFormatterParameter($formats[$i], 'output_decorate', $decorated);
            }
        }
    }

    /**
     * Checks if provided output identifier represents standard output.
     *
     * @param string $outputId
     *
     * @return bool
     */
    private function isStandardOutput($outputId)
    {
        return 'std' === $outputId || 'null' === $outputId || 'false' === $outputId;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return bool
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

    /**
     * Returns formatters description.
     *
     * @return string
     */
    private function getFormatterDescriptions()
    {
        return implode(
            PHP_EOL,
            array_map(
                function (Formatter $formatter) {
                    $comment = '- <comment>' . $formatter->getName() . '</comment>: ';
                    $comment .= $formatter->getDescription();

                    return $comment;
                }, $this->manager->getFormatters()
            )
        ) . PHP_EOL;
    }

    /**
     * Loads JSON settings as formatter parameters.
     *
     * @param string $jsonSettings
     */
    private function loadJsonSettings($jsonSettings)
    {
        $settings = @json_decode($jsonSettings, true);

        if (!is_array($settings)) {
            return;
        }

        foreach ($settings as $name => $value) {
            $this->manager->setFormattersParameter($name, $value);
        }
    }
}
