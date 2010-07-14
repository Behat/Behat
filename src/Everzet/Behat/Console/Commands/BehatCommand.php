<?php

namespace Everzet\Behat\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;
use \Symfony\Components\Finder\Finder;

use \Everzet\Gherkin\I18n;
use \Everzet\Behat\FeatureRuner;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Environment\SimpleWorld;
use \Everzet\Behat\Printers\ConsolePrinter;
use \Everzet\Behat\Stats\TestStats;
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
class BehatCommand extends Command
{
    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('behat');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', 'features')
        ));
    }

    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = realpath($input->getArgument('features'));
        if (is_dir($basePath.'/features')) {
            $basePath .= '/features';
        }

        // Init steps container
        $steps = new StepsContainer();

        // Init World object
        $world = new SimpleWorld($basePath . '/support/env.php');

        // Read steps definition from files
        $finder = new Finder();
        $stepsFiles = $finder->files()->name('*.php')->in($basePath . '/steps');
        try {
            foreach ($stepsFiles as $stepsFile) {
                require $stepsFile;
            }
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>\n",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ));
            exit;
        }

        // Read feature files
        $finder = new Finder();
        $featureFiles = $finder->files()->name('*.feature')->in($input->getArgument('features'));

        // Init I18n for Gherkin with translations path
        $i18n = new I18n(realpath(__DIR__ . '/../../../../../i18n'));

        // Init test printer
        $printer = new ConsolePrinter($output, $i18n, $basePath, $input->getOption('verbose'));

        // Init statistics container
        $stats = new TestStats;

        foreach ($featureFiles as $featureFile) {
            $runer = new FeatureRuner($featureFile, $printer, $steps, $world, $i18n);
            $stats->addFeatureStatuses($runer->run());
            $output->writeln('');
        }

        $printer->logStats($stats, $steps);
    }
}
