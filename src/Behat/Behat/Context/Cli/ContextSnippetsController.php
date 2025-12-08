<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Cli;

use Behat\Behat\Context\Snippet\Generator\AggregateContextIdentifier;
use Behat\Behat\Context\Snippet\Generator\AggregatePatternIdentifier;
use Behat\Behat\Context\Snippet\Generator\ContextInterfaceBasedContextIdentifier;
use Behat\Behat\Context\Snippet\Generator\ContextInterfaceBasedPatternIdentifier;
use Behat\Behat\Context\Snippet\Generator\ContextSnippetGenerator;
use Behat\Behat\Context\Snippet\Generator\FixedContextIdentifier;
use Behat\Behat\Context\Snippet\Generator\FixedPatternIdentifier;
use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures which context snippets are generated for.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippetsController implements Controller
{
    /**
     * Initialises controller.
     */
    public function __construct(
        private readonly ContextSnippetGenerator $generator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configure(SymfonyCommand $command): void
    {
        $command
            ->addOption(
                '--snippets-for',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specifies which context class to generate snippets for.'
            )
            ->addOption(
                '--snippets-type',
                null,
                InputOption::VALUE_REQUIRED,
                'Specifies which type of snippets (turnip, regex) to generate.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generator->setContextIdentifier(
            new AggregateContextIdentifier([
                new ContextInterfaceBasedContextIdentifier(),
                new FixedContextIdentifier($input->getOption('snippets-for')),
                new InteractiveContextIdentifier($this->translator, $input, $output),
            ])
        );

        $this->generator->setPatternIdentifier(
            new AggregatePatternIdentifier([
                new ContextInterfaceBasedPatternIdentifier(),
                new FixedPatternIdentifier($input->getOption('snippets-type')),
            ])
        );

        return null;
    }
}
