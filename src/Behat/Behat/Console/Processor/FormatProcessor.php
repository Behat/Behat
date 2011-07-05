<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class FormatProcessor implements ProcessorInterface
{
    /**
     * Default Behat formatters.
     *
     * @var     array
     */
    private $defaultFormatters = array(
        'pretty'    => 'Behat\Behat\Formatter\PrettyFormatter',
        'progress'  => 'Behat\Behat\Formatter\ProgressFormatter',
        'html'      => 'Behat\Behat\Formatter\HtmlFormatter',
        'junit'     => 'Behat\Behat\Formatter\JUnitFormatter'
    );

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::getInputOptions()
     */
    public function getInputOptions()
    {
        return array(
            new InputOption('--format',         '-f',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'How to format features. <comment>pretty</comment> is default. Available formats are ' .
                implode(', ',
                    array_map(function($name) {
                        return "<comment>$name</comment>";
                    }, array_keys($this->defaultFormatters))
                )
            ),
            new InputOption('--out',            null,
                InputOption::VALUE_REQUIRED,
                '          ' .
                'Write formatter output to a file/directory instead of STDOUT (<comment>output_path</comment>).'
            ),
            new InputOption('--colors',         null,
                InputOption::VALUE_NONE,
                '       ' .
                'Force Behat to use ANSI color in the output.'
            ),
            new InputOption('--no-colors',      null,
                InputOption::VALUE_NONE,
                '    ' .
                'Do not use ANSI color in the output.'
            ),
            new InputOption('--no-time',        null,
                InputOption::VALUE_NONE,
                '      ' .
                'Hide time in output.'
            ),
            new InputOption('--lang',           null,
                InputOption::VALUE_REQUIRED,
                '         ' .
                'Print formatter output in particular language.'
            ),
            new InputOption('--no-paths',       null,
                InputOption::VALUE_NONE,
                '     ' .
                'Do not print the definition path with the steps.'
            ),
            new InputOption('--no-snippets',    null,
                InputOption::VALUE_NONE,
                '  ' .
                'Do not print snippets for undefined steps.'
            ),
            new InputOption('--no-multiline',   null,
                InputOption::VALUE_NONE,
                ' ' .
                'No multiline arguments in output.'
            ),
            new InputOption('--expand',         null,
                InputOption::VALUE_NONE,
                '       ' .
                'Expand Scenario Outline Tables in output.'."\n"
            ),
        );
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $locator = $container->get('behat.path_locator');
        $translator = $container->get('behat.translator');
        $eventDispatcher = $container->get('behat.event_dispatcher');

        $formatter = $this->createFormatter(
            $input->getOption('format') ?: $container->getParameter('behat.formatter.name')
        );

        // configure formatter
        $formatter->setTranslator($translator);
        $formatter->setParameter('base_path', $locator->getWorkPath());
        $formatter->setParameter('support_path', $locator->getBootstrapPath());
        $formatter->setParameter('decorated', $output->isDecorated());
        foreach ($container->getParameter('behat.formatter.parameters') as $param => $value) {
            $formatter->setParameter($param, $value);
        }
        if ($input->getOption('verbose')) {
            $formatter->setParameter('verbose', true);
        }
        if ($input->getOption('lang')) {
            $formatter->setParameter('language', $input->getOption('lang'));
        }
        if ($input->getOption('colors')) {
            $output->setDecorated(true);
            $formatter->setParameter('decorated', true);
        } elseif ($input->getOption('no-colors')) {
            $output->setDecorated(false);
            $formatter->setParameter('decorated', false);
        }
        if ($input->getOption('no-time')) {
            $formatter->setParameter('time', false);
        }
        if ($input->getOption('no-snippets')) {
            $formatter->setParameter('snippets', false);
        }
        if ($input->getOption('no-paths')) {
            $formatter->setParameter('paths', false);
        }
        if ($input->getOption('expand')) {
            $formatter->setParameter('expand', true);
        }
        if ($input->getOption('no-multiline')) {
            $formatter->setParameter('multiline_arguments', false);
        }
        if ($out = $input->getOption('out')
         ?: $locator->getOutputPath($formatter->getParameter('output_path'))) {
            // get realpath
            if (!file_exists($out)) {
                touch($out);
                $out = realpath($out);
                unlink($out);
            } else {
                $out = realpath($out);
            }
            $formatter->setParameter('output_path', $out);
            $formatter->setParameter('decorated', (Boolean) $input->getOption('colors'));
        }

        $eventDispatcher->addSubscriber($formatter, -10);
    }

    /**
     * Creates formatter with provided input.
     *
     * @param   string  $formatterName  formatter name or class
     *
     * @return  Behat\Behat\Formatter\FormatterInterface
     *
     * @throws  RuntimeException            if provided in input formatter name doesn't exists
     */
    private function createFormatter($formatterName)
    {
        if (class_exists($formatterName)) {
            $class = $formatterName;
        } elseif (isset($this->defaultFormatters[$formatterName])) {
            $class = $this->defaultFormatters[$formatterName];
        } else {
            throw new \RuntimeException("Unknown formatter: \"$formatterName\". " .
                'Available formatters are: ' . implode(', ', array_keys($this->defaultFormatters))
            );
        }

        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface('Behat\Behat\Formatter\FormatterInterface')) {
            throw new \RuntimeException(sprintf(
                'Formatter class "%s" should implement FormatterInterface', $class
            ));
        }

        return new $class();
    }
}
