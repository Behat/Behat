<?php

namespace Everzet\Behat\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;
use \Symfony\Components\Finder\Finder;

use \Everzet\Gherkin\Feature;
use \Everzet\Gherkin\Background;
use \Everzet\Gherkin\Scenario;
use \Everzet\Gherkin\ScenarioOutline;
use \Everzet\Behat\FeatureRuner;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Environment\SimpleWorld;
use \Everzet\Behat\Printers\ConsolePrinter;
use \Everzet\Behat\Exceptions\Redundant;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console test command.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestCommand extends Command
{
    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('test');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', './features')
        ));
    }

    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = realpath(dirname($input->getArgument('features')));

        // Init test printer
        $printer = new ConsolePrinter($output, $basePath, $input->getOption('verbose'));

        // Read steps definition from files
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($basePath . '/steps');
        $steps = new StepsContainer();
        $world = new SimpleWorld($basePath . '/support/env.php');
        try {
            foreach ($files as $file) {
                require $file;
            }
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>\n",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ));
            exit;
        }

        // Read feature files
        $finder = new Finder();
        $files = $finder->files()->name('*.feature')->in($input->getArgument('features'));

        foreach ($files as $file) {
            $runer = new FeatureRuner($file, $printer, $steps, &$world);
            $runer->run();
            $output->writeln('');
        }
    }
}
