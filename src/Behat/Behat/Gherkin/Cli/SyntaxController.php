<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Cli;

use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Gherkin\Keywords\KeywordsDumper;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints example of the feature to present all available syntax keywords.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SyntaxController implements Controller
{
    /**
     * @var KeywordsDumper
     */
    private $keywordsDumper;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes controller.
     *
     * @param KeywordsDumper      $dumper
     * @param TranslatorInterface $translator
     */
    public function __construct(KeywordsDumper $dumper, TranslatorInterface $translator)
    {
        $dumper->setKeywordsDumperFunction(array($this, 'dumpKeywords'));
        $this->keywordsDumper = $dumper;
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
                '--story-syntax', null, InputOption::VALUE_NONE,
                "Print <comment>*.feature</comment> example." . PHP_EOL .
                "Use <info>--lang</info> to see specific language."
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
        if (!$input->getOption('story-syntax')) {
            return null;
        }

        $output->getFormatter()->setStyle('gherkin_keyword', new OutputFormatterStyle('green', null, array('bold')));
        $output->getFormatter()->setStyle('gherkin_comment', new OutputFormatterStyle('yellow'));

        $story = $this->keywordsDumper->dump($this->translator->getLocale());
        $story = preg_replace('/^\#.*/', '<gherkin_comment>$0</gherkin_comment>', $story);
        $output->writeln($story);
        $output->writeln('');

        return 0;
    }

    /**
     * Keywords dumper.
     *
     * @param array $keywords keywords list
     *
     * @return string
     */
    public function dumpKeywords(array $keywords)
    {
        $dump = '<gherkin_keyword>' . implode('</gherkin_keyword>|<gherkin_keyword>', $keywords) . '</gherkin_keyword>';

        if (1 < count($keywords)) {
            return '[' . $dump . ']';
        }

        return $dump;
    }
}
