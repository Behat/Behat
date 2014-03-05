<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

/**
 * Behat skipped step printer interface.
 *
 * Exactly the same printer and default step printer, except that it marks all steps as skipped.
 * Used mostly for outline header printing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SkippedStepPrinter extends StepPrinter
{
}
