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
        $featureFiles = array();

        if (is_dir($basePath.'/features')) {
            $basePath .= '/features';
        } elseif (is_file($basePath)) {
            $featureFiles[] = $basePath;
            $basePath = dirname($basePath);
        }

        // Init I18n for Gherkin with translations path
        $i18n = new I18n(realpath(__DIR__ . '/../../../../../i18n'));

        // Init test printer
        $printer = new ConsolePrinter($output, $i18n, $basePath, $input->getOption('verbose'));

        // Sets world class name & environment config path
        $worldClass = 'Everzet\Behat\Environment\SimpleWorld';
        $worldClass::setEnvFile($basePath . '/support/env.php');

        // Find step definition files
        $finder = new Finder();
        $stepDefinitions = $finder->files()->name('*.php')->in($basePath . '/steps')->getIterator();

        // Check if we had redundant definitions
        try {
            new StepsContainer($stepDefinitions);
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ));
            return 1;
        }

        // Read feature files
        if (!count($featureFiles)) {
            $finder = new Finder();
            $featureFiles = $finder->files()->name('*.feature')->in(
                $input->getArgument('features')
            );
        }

        // Init statistics container
        $stats = new TestStats;
        foreach ($featureFiles as $featureFile) {
            $runer = new FeatureRuner(
                $featureFile, $printer, $stepDefinitions, $worldClass, $i18n
            );
            $stats->addFeatureStatuses($runer->run());
        }

        $printer->logStats($stats, $stepDefinitions);
    }
}
