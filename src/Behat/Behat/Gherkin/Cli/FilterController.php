<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Cli;

use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Gherkin;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures default Gherkin filters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FilterController implements Controller
{
    /**
     * @var Gherkin
     */
    private $gherkin;

    /**
     * Initializes controller.
     *
     * @param Gherkin $gherkin
     */
    public function __construct(Gherkin $gherkin)
    {
        $this->gherkin = $gherkin;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                "Only executeCall the feature elements which match part" . PHP_EOL .
                "of the given name or regex."
            )
            ->addOption(
                '--tags', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                "Only executeCall the features or scenarios with tags" . PHP_EOL .
                "matching tag filter expression."
            )
            ->addOption(
                '--role', null, InputOption::VALUE_REQUIRED,
                "Only executeCall the features with actor role matching" . PHP_EOL .
                "a wildcard."
            );
    }

    /**
     * Executes controller.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filters = array();

        foreach ($input->getOption('name') as $name) {
            $filters[] = new NameFilter($name);
        }

        foreach ($input->getOption('tags') as $tags) {
            $filters[] = new TagFilter($tags);
        }

        if ($role = $input->getOption('role')) {
            $filters[] = new RoleFilter($role);
        }

        if (count($filters)) {
            $this->gherkin->setFilters($filters);
        }
    }
}
