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
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\ReferenceDumper;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Extends Symfony console application with testwork functionality.
 *
 * @author Christophe Coevoet <stof>
 */
final class DumpReferenceCommand extends BaseCommand
{
    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * Initializes dumper.
     *
     * @param ExtensionManager $extensionManager
     */
    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;

        parent::__construct('dump-reference');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = new YamlReferenceDumper();
        $configTree = new ConfigurationTree();

        $output->writeln($dumper->dumpNode($configTree->getConfigTree($this->extensionManager->getExtensions())));

        return 0;
    }
}
