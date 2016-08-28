<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Cli;

use Behat\Behat\Context\Snippet\Generator\ContextSnippetGenerator;
use Behat\Behat\Context\Snippet\Generator\FixedContextIdentifier;
use Behat\Behat\Context\Snippet\Generator\FixedPatternIdentifier;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Configures which context snippets are generated for.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippetsController implements Controller
{
    /**
     * @var ContextSnippetGenerator
     */
    private $generator;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initialises controller.
     *
     * @param ContextSnippetGenerator $generator
     * @param TranslatorInterface     $translator
     */
    public function __construct(ContextSnippetGenerator $generator, TranslatorInterface $translator)
    {
        $this->generator = $generator;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(SymfonyCommand $command)
    {
        $command
            ->addOption(
                '--snippets-for', null, InputOption::VALUE_OPTIONAL,
                "Specifies which context class to generate snippets for."
            )
            ->addOption(
                '--snippets-type', null, InputOption::VALUE_REQUIRED,
                "Specifies which type of snippets (turnip, regex) to generate."
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--snippets-for')) {
            $identifier =
                null !== $input->getOption('snippets-for')
                    ? new FixedContextIdentifier($input->getOption('snippets-for'))
                    : new InteractiveContextIdentifier($this->translator, $input, $output);

            $this->generator->setContextIdentifier($identifier);
        }

        if (null !== $input->getOption('snippets-type')) {
            $this->generator->setPatternIdentifier(new FixedPatternIdentifier($input->getOption('snippets-type')));
        }
    }
}
