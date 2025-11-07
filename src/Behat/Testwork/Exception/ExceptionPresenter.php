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
use Behat\Testwork\Output\Printer\OutputPrinter;
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
     * @var ExceptionStringer[]
     */
    private $stringers = [];

    private readonly ConfigurablePathPrinter $configurablePathPrinter;

    /**
     * Initializes presenter.
     *
     * @param ?string $basePath deprecated, will be removed in next major version
     */
    public function __construct(
        ?string $basePath = null,
        private int $defaultVerbosity = OutputPrinter::VERBOSITY_NORMAL,
        ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
        // Historically, this class accepted a null (or not present) value for basePath. This was never passed by Behat,
        // but was used by third parties to force the class to render exception traces with absolute file paths.
        // The ConfigurablePathPrinter requires a value, but it is not used if printAbsolutePaths is true.
        // Therefore, if the user provided null we can safely cast to '' (the working directory) and tell the printer
        // to show absolute paths.
        $this->configurablePathPrinter = $configurablePathPrinter ?? new ConfigurablePathPrinter($basePath ?? '', printAbsolutePaths: $basePath === null, editorUrl: null);
    }

    /**
     * Registers exception stringer.
     */
    public function registerExceptionStringer(ExceptionStringer $stringer)
    {
        $this->stringers[] = $stringer;
    }

    /**
     * Sets default verbosity to a specified level.
     *
     * @param int $defaultVerbosity
     */
    public function setDefaultVerbosity($defaultVerbosity)
    {
        $this->defaultVerbosity = $defaultVerbosity;
    }

    /**
     * Presents exception as a string.
     */
    public function presentException(
        Throwable $exception,
        ?int $verbosity = null,
        $applyEditorUrl = true,
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

    private function removeBehatCallsFromTrace(Exception $exception)
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
