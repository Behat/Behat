<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception;

use Behat\Testwork\Call\Exception\FatalThrowableError;
use Behat\Testwork\Exception\Stringer\ExceptionStringer;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Presents exceptions as strings using registered stringers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExceptionPresenter
{
    /**
     * @var list<ExceptionStringer>
     */
    private array $stringers = [];

    public function __construct(
        private int $defaultVerbosity,
        private readonly ConfigurablePathPrinter $configurablePathPrinter,
    ) {
    }

    /**
     * Registers exception stringer.
     */
    public function registerExceptionStringer(ExceptionStringer $stringer): void
    {
        $this->stringers[] = $stringer;
    }

    /**
     * Sets default verbosity to a specified level.
     */
    public function setDefaultVerbosity(int $defaultVerbosity): void
    {
        $this->defaultVerbosity = $defaultVerbosity;
    }

    /**
     * Presents exception as a string.
     */
    public function presentException(
        Throwable $exception,
        ?int $verbosity = null,
        bool $applyEditorUrl = true,
    ): string {
        $verbosity = $verbosity ?: $this->defaultVerbosity;

        if (!$exception instanceof Exception) {
            $exception = new FatalThrowableError($exception);
        }

        foreach ($this->stringers as $stringer) {
            if ($stringer->supportsException($exception)) {
                return $this->configurablePathPrinter->processPathsInText(
                    $stringer->stringException($exception, $verbosity),
                    applyEditorUrl: $applyEditorUrl
                );
            }
        }

        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $verbosity) {
            if (OutputInterface::VERBOSITY_DEBUG > $verbosity) {
                $exception = $this->removeBehatCallsFromTrace($exception);
            }

            return $this->configurablePathPrinter->processPathsInText(trim((string) $exception));
        }

        return trim($this->configurablePathPrinter->processPathsInText($exception->getMessage()) . ' (' . $exception::class . ')');
    }

    private function removeBehatCallsFromTrace(Exception $exception): string
    {
        $traceOutput = '';
        foreach ($exception->getTrace() as $i => $trace) {
            if (isset($trace['file']) && str_contains(str_replace('\\', '/', $trace['file']), 'Behat/Testwork/Call/Handler/RuntimeCallHandler')) {
                break;
            }

            $traceOutput .= sprintf(
                '#%d %s: %s()' . PHP_EOL,
                $i,
                isset($trace['file']) ? $trace['file'] . '(' . $trace['line'] . ')' : '[internal function]',
                isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function']
            );
        }

        return sprintf(
            '%s: %s in %s:%d%sStack trace:%s%s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL,
            PHP_EOL,
            $traceOutput
        );
    }
}
