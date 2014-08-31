<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ContextClass;

use Behat\Testwork\Suite\Suite;

/**
 * Generates basic PHP 5.3+ class with an optional namespace.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SimpleClassGenerator implements ClassGenerator
{
    /**
     * @var string
     */
    protected static $template = <<<'PHP'
<?php

{namespace}use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class {className} implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
}

PHP;

    /**
     * {@inheritdoc}
     */
    public function supportsSuiteAndClass(Suite $suite, $contextClass)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generateClass(Suite $suite, $contextClass)
    {
        $fqn = $contextClass;

        $namespace = '';
        if (false !== $pos = strrpos($fqn, '\\')) {
            $namespace = 'namespace ' . substr($fqn, 0, $pos) . ";\n\n";
            $contextClass = substr($fqn, $pos + 1);
        }

        return strtr(
            static::$template,
            array(
                '{namespace}' => $namespace,
                '{className}' => $contextClass,
            )
        );
    }
}
