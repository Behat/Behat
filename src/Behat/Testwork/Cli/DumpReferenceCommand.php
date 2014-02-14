<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use Behat\Testwork\ServiceContainer\Configuration\ConfigurationTree;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\ReferenceDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpReferenceCommand extends Command
{
    private $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;

        parent::__construct('dump-reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = new ReferenceDumper();
        $configTree = new ConfigurationTree();

        $output->writeln($dumper->dumpNode($configTree->getConfigTree($this->extensionManager->getExtensions())));
    }
}
