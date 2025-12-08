<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Translator\Cli;

use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;

/**
 * Configures translator service and loads default translations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinTranslationsController implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    public function configure(SymfonyCommand $command): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $i18nPath = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . 'i18n.php';

        foreach (require ($i18nPath) as $lang => $messages) {
            $this->translator->addResource('array', $messages, $lang, 'output');
        }

        return null;
    }
}
