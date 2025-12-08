<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Appender;

use Behat\Behat\Snippet\AggregateSnippet;
use Behat\Behat\Snippet\Appender\SnippetAppender;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Filesystem\FilesystemLogger;
use ReflectionClass;

/**
 * Appends context-related snippets to their context classes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippetAppender implements SnippetAppender
{
    public const PENDING_EXCEPTION_CLASS = PendingException::class;

    public function __construct(
        private readonly ?FilesystemLogger $logger = null,
    ) {
    }

    public function supportsSnippet(AggregateSnippet $snippet): bool
    {
        return 'context' === $snippet->getType();
    }

    public function appendSnippet(AggregateSnippet $snippet): void
    {
        foreach ($snippet->getTargets() as $contextClass) {
            $reflection = new ReflectionClass($contextClass);
            $content = file_get_contents($reflection->getFileName());

            foreach ($snippet->getUsedClasses() as $class) {
                if (!$this->isClassImported($class, $content)) {
                    $content = $this->importClass($class, $content);
                }
            }

            $generated = rtrim(strtr($snippet->getSnippet(), ['\\' => '\\\\', '$' => '\\$']));
            $content = preg_replace('/}\s*$/', "\n" . $generated . "\n}\n", $content);
            $path = $reflection->getFileName();

            file_put_contents($path, $content);

            $this->logSnippetAddition($snippet, $path);
        }
    }

    /**
     * Checks if context file already has class in it.
     *
     * @param string $class
     * @param string $contextFileContent
     */
    private function isClassImported($class, $contextFileContent): bool
    {
        $classImportRegex = sprintf(
            '@use[^;]*%s.*;@ms',
            preg_quote($class, '@')
        );

        return 1 === preg_match($classImportRegex, $contextFileContent);
    }

    /**
     * Adds use-block for class.
     *
     * @param string $class
     * @param string $contextFileContent
     *
     * @return string
     */
    private function importClass($class, $contextFileContent)
    {
        $replaceWith = '$1use ' . $class . ";\n\$2;";

        return preg_replace('@^(.*)(use\s+[^;]*);@m', $replaceWith, $contextFileContent, 1);
    }

    /**
     * Logs snippet addition to the provided path (if logger is given).
     *
     * @param string           $path
     */
    private function logSnippetAddition(AggregateSnippet $snippet, $path): void
    {
        if (!$this->logger instanceof FilesystemLogger) {
            return;
        }

        $steps = $snippet->getSteps();
        $reason = sprintf('`<comment>%s</comment>` definition added', $steps[0]->getText());

        $this->logger->fileUpdated($path, $reason);
    }
}
