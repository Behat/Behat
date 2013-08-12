<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Gherkin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Gherkin filters processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinFilterProcessor implements ProcessorInterface
{
    /**
     * @var Gherkin
     */
    private $gherkin;

    /**
     * Initializes processor.
     *
     * @param Gherkin $gherkin
     */
    public function __construct(Gherkin $gherkin)
    {
        $this->gherkin = $gherkin;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--name', null, InputOption::VALUE_REQUIRED,
                "Only execute the feature elements which match" . PHP_EOL .
                "part of the given name or regex."
            )
            ->addOption('--tags', null, InputOption::VALUE_REQUIRED,
                "Only execute the features or scenarios with tags" . PHP_EOL .
                "matching tag filter expression."
            )
            ->addOption('--role', null, InputOption::VALUE_REQUIRED,
                "Only execute the features with actor role" . PHP_EOL .
                "matching wildcard."
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
        if ($name = $input->getOption('name')) {
            $this->gherkin->addFilter(new NameFilter($name));
        }
        if ($tags = $input->getOption('tags')) {
            $this->gherkin->addFilter(new TagFilter($tags));
        }
        if ($role = $input->getOption('role')) {
            $this->gherkin->addFilter(new RoleFilter($role));
        }
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 60;
    }
}
