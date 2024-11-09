<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Exception;
use Throwable;

/**
 * Strings PHPUnit assertion exceptions.
 *
 * @see ExceptionPresenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PHPUnitExceptionStringer implements ExceptionStringer
{
    /**
     * {@inheritdoc}
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof \PHPUnit_Framework_Exception
            || $exception instanceof \PHPUnit\Framework\Exception;
    }

    /**
     * {@inheritdoc}
     */
    public function stringException(Exception $exception, $verbosity)
    {
        // PHPUnit assertion exceptions do not include detailed expected / observed info in their messages. Instead,
        // test result printers within PHPUnit are expected to format and present that information separately. The
        // mechanism for this varies between PHPUnit major versions, and all the implementations are tagged with:
        //
        //   *  @internal This class is not covered by the backward compatibility promise for PHPUnit`
        //
        // Behat itself does not use PHPUnit at runtime, and user projects may use PHPUnit solely for unit tests with
        // a completely separate assertion mechanism for their Behat steps.
        //
        // Therefore, Behat does not impose any formal PHPUnit version constraints.
        //
        // Instead, we make a best effort to render as much detail of a PHPUnit assertion failure as we can, without
        // masking that the ultimate problem was caused by a failed assertion in the user's own code.
        //
        // **
        // * We cannot guarantee that this will work, or produce the same output, even across minor PHPUnit versions.
        // * That said, historically this has been relatively stable for a given major version series.
        // **
        //
        // If you encounter a problem rendering PHPUnit assertions in your project, you have three options:
        //
        // * Roll back to a PHPUnit minor / patch version that you know works for you.
        // * Catch the failures within your Context classes and format them yourself. For example, you could implement
        //   a generic wrapper to call like `MyClass::formatFailure(fn () => Assert::assertSame(1, 2, 'Uh-oh'))`.
        // * Contribute a PR to Behat to add support for the newer PHPUnit version :)

        try {
            // Class names are intentionally fully qualified here to maximise clarity - particularly because a future
            // PHPUnit version may reuse a class name in a different namespace.

            if (class_exists(\PHPUnit\Util\ThrowableToStringMapper::class)) {
                // PHPUnit 10.0.0 onwards
                return trim(\PHPUnit\Util\ThrowableToStringMapper::map($exception));
            }

            if (class_exists(\PHPUnit\Framework\TestFailure::class)) {
                // PHPUnit 6.0.0 - 9.x
                return trim(\PHPUnit\Framework\TestFailure::exceptionToString($exception));
            }

            if (class_exists(\PHPUnit_Framework_TestFailure::class)) {
                // PHPUnit < 6 (support ended in 2016)
                return trim(\PHPUnit_Framework_TestFailure::exceptionToString($exception));
            }

            // PHPUnit must be present, because we got a PHPUnit exception. So it must be a newer version with a
            // formatter class / method we don't know about.
            return sprintf(
                <<<TEXT
                %s
                !! Could not render more details of this %s.
                   Behat does not support automatically formatting assertion failures for your PHPUnit version.
                   See %s for details.
                TEXT,
                $exception->getMessage(),
                $exception::class,
                self::class,
            );
        } catch (Throwable $phpunitException) {
            // PHPUnit does not guarantee BC on the classes / methods we're calling.
            //
            // So it is likely that it looked like a version we expected, but the method signature or internal typing
            // has changed, causing an error at runtime.
            //
            // It is also possible that there's something in the user input (e.g. a value passed to $expect) that is
            // causing an error when PHPUnit tries to stringify it - but PHPUnit is generally quite robust about
            // catching those situations and reporting them within the message body.
            return sprintf(
                <<<TEXT
                %s
                !! There was an error trying to render more details of this %s.
                   You are probably using a PHPUnit version that Behat cannot automatically display failures for.
                   See %s for details of PHPUnit support.
                   [%s] %s at %s:%s
                TEXT,
                $exception->getMessage(),
                $exception::class,
                self::class,
                $phpunitException::class,
                $phpunitException->getMessage(),
                $phpunitException->getFile(),
                $phpunitException->getLine(),
            );
        }
    }
}
