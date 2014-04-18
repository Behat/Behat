<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Translator\Cli;

use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;

/**
 * Configures translator service to use custom locale.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class LanguageController implements Controller
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Initializes controller.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command->addOption('--lang', null, InputOption::VALUE_REQUIRED,
            'Print output in particular language.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('lang')) {
            return;
        }

        $this->translator->setLocale($input->getOption('lang'));
    }
}
