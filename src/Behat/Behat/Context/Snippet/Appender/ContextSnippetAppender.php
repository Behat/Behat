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
use Behat\Behat\Util\StrictRegex;
use Behat\Testwork\Filesystem\FilesystemLogger;
use ReflectionClass;
use RuntimeException;

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
            $contextFilePath = $this->getFilePath($contextClass);
            $content = file_get_contents($contextFilePath);
            if ($content === false) {
                throw new RuntimeException('Failed to read context file: ' . $contextFilePath);
            }

            foreach ($snippet->getUsedClasses() as $class) {
                if (!$this->isClassImported($class, $content)) {
                    $content = $this->importClass($class, $content);
                }
            }

            $generated = rtrim(strtr($snippet->getSnippet(), ['\\' => '\\\\', '$' => '\\$']));
            $content = StrictRegex::replace('/}\s*$/', "\n" . $generated . "\n}\n", $content);

            file_put_contents($contextFilePath, $content);

            $this->logSnippetAddition($snippet, $contextFilePath);
        }
    }

    private function getFilePath(string $contextClass): string
    {
        $reflection = new ReflectionClass($contextClass);
        $path = $reflection->getFileName();

        if ($path === false) {
            throw new RuntimeException(sprintf('Failed to get file path for context class "%s".', $contextClass));
        }

        return $path;
    }

    /**
     * Checks if context file already has class in it.
     */
    private function isClassImported(string $class, string $contextFileContent): bool
    {
        $classImportRegex = sprintf(
            '@use[^;]*%s.*;@ms',
            preg_quote($class, '@')
        );

        return 1 === preg_match($classImportRegex, $contextFileContent);
    }

    /**
     * Adds use-block for class.
     */
    private function importClass(string $class, string $contextFileContent): string
    {
        $replaceWith = '$1use ' . $class . ";\n\$2;";

        return StrictRegex::replace('@^(.*)(use\s+[^;]*);@m', $replaceWith, $contextFileContent, 1);
    }

    /**
     * Logs snippet addition to the provided path (if logger is given).
     */
    private function logSnippetAddition(AggregateSnippet $snippet, string $path): void
    {
        if (!$this->logger instanceof FilesystemLogger) {
            return;
        }

        $steps = $snippet->getSteps();
        $reason = sprintf('`<comment>%s</comment>` definition added', $steps[0]->getText());

        $this->logger->fileUpdated($path, $reason);
    }
}
