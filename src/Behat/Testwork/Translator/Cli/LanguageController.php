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
 * Testwork language controller.
 *
 * Configures translator service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LanguageController implements Controller
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
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--lang', null, InputOption::VALUE_REQUIRED,
                'Print output in particular language.'
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
        if (!$input->getOption('lang') && !$this->getDefaultLanguage()) {
            return;
        }

        $this->translator->setLocale($input->getOption('lang') ?: $this->getDefaultLanguage());
    }

    /**
     * Tries to guess default user cli language.
     *
     * @return null|string
     */
    protected function getDefaultLanguage()
    {
        $defaultLanguage = null;
        if (($locale = getenv('LANG')) && preg_match('/^([a-z]{2})/', $locale, $matches)) {
            $defaultLanguage = $matches[1];

            return $defaultLanguage;
        }

        return $defaultLanguage;
    }
}
