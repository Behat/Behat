<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * command configuration processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RunProcessor extends Processor
{
    private $container;

    /**
     * Constructs processor.
     *
     * @param ContainerInterface $container Container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--strict', null, InputOption::VALUE_NONE,
                'Fail if there are any undefined or pending steps.'
            )
            ->addOption('--dry-run', null, InputOption::VALUE_NONE,
                'Invokes formatters without executing the steps & hooks.'
            )
            ->addOption('--stop-on-failure', null, InputOption::VALUE_NONE,
                'Stop processing on first failed scenario.'
            )
            ->addOption('--rerun', null, InputOption::VALUE_REQUIRED,
                "Save list of failed scenarios into new file\n" .
                "or use existing file to run only scenarios from it."
            )
            ->addOption('--append-snippets', null, InputOption::VALUE_NONE,
                "Appends snippets for undefined steps into main context."
            )
            ->addOption('--append-to', null, InputOption::VALUE_REQUIRED,
                "Appends snippets for undefined steps into specified class."
            )
        ;
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $command        = $this->container->get('behat.console.command');
        $hookDispatcher = $this->container->get('behat.hook.dispatcher');

        $command->setStrict(
            $input->getOption('strict') || $this->container->getParameter('behat.options.strict')
        );
        $command->setDryRun(
            $input->getOption('dry-run') || $this->container->getParameter('behat.options.dry_run')
        );
        $hookDispatcher->setDryRun(
            $input->getOption('dry-run') || $this->container->getParameter('behat.options.dry_run')
        );

        if ($file = $input->getOption('rerun') ?: $this->container->getParameter('behat.options.rerun')) {
            if (file_exists($file)) {
                $command->setFeaturesPaths(explode("\n", trim(file_get_contents($file))));
            }

            $this->container->get('behat.formatter.manager')
                ->initFormatter('failed')
                ->setParameter('output_path', $file);
        }

        if ($input->getOption('append-snippets')) {
            $this->initializeSnippetsAppender();
        } elseif ($class = $input->getOption('append-to')) {
            $this->initializeSnippetsAppender($class);
        } elseif ($class = $this->container->getParameter('behat.options.append_snippets')) {
            $this->initializeSnippetsAppender(true !== $class ? $class : null);
        }

        if ($input->getOption('stop-on-failure') || $this->container->getParameter('behat.options.stop_on_failure')) {
            $this->initializeStopOnFailure();
        }
    }

    /**
     * Appends snippets to the main context after suite run.
     *
     * @param string $class
     */
    protected function initializeSnippetsAppender($class = null)
    {
        $classname = (null !== $class)
            ? str_replace('/', '\\', $class)
            : $this->container->get('behat.context.dispatcher')->getContextClass()
        ;

        $contextRefl = new \ReflectionClass($classname);
        if ($contextRefl->implementsInterface('Behat\Behat\Context\ClosuredContextInterface')) {
            throw new \RuntimeException(
                '--append-snippets doesn\'t support closured contexts'
            );
        }

        $formatManager = $this->container->get('behat.formatter.manager');
        $formatManager->setFormattersParameter('snippets', false);

        $formatter = $formatManager->initFormatter('snippets');
        $formatter->setParameter('decorated', false);
        $formatter->setParameter('output_decorate', false);
        $formatter->setParameter('output', $snippets = fopen('php://memory', 'rw'));

        $this->container->get('behat.event_dispatcher')
            ->addListener('afterSuite', function() use($contextRefl, $snippets) {
                rewind($snippets);
                $snippets = stream_get_contents($snippets);

                if (trim($snippets)) {
                    $snippets = strtr($snippets, array('\\' => '\\\\', '$' => '\\$'));
                    $context  = file_get_contents($contextRefl->getFileName());
                    $context  = preg_replace('/}[ \n]*$/', rtrim($snippets)."\n}\n", $context);

                    file_put_contents($contextRefl->getFileName(), $context);
                }
            }, -5);
    }

    /**
     * Adds listener to detect failed scenario and then triggers command to abort the suite run.
     */
    protected function initializeStopOnFailure()
    {
        $command = $this->container->get('behat.console.command');
        
        $this->container->get('behat.event_dispatcher')
            ->addListener('afterScenario', function ($scenarioEvent) use ($command) {
                if ($scenarioEvent->getResult() === StepEvent::FAILED) {
                    $command->abortSuite();
                }
            });
    }

}
