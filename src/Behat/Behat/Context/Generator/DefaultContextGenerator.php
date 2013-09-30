<?php

namespace Behat\Behat\Context\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\GherkinSuite;
use Behat\Behat\Suite\SuiteInterface;

/**
 * Default context generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefaultContextGenerator implements GeneratorInterface
{
    /**
     * Checks if generator supports specific suite and context class.
     *
     * @param SuiteInterface $suite
     * @param string         $classname
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, $classname)
    {
        return $suite instanceof GherkinSuite;
    }

    /**
     * Generates context class code.
     *
     * @param SuiteInterface $suite
     * @param string         $fqn
     *
     * @return string
     */
    public function generate(SuiteInterface $suite, $fqn)
    {
        $namespace = '';
        $classname = $fqn;
        if (false !== $pos = strrpos($fqn, '\\')) {
            $namespace = 'namespace ' . substr($fqn, 0, $pos) . ';' . PHP_EOL . PHP_EOL;
            $classname = substr($fqn, $pos + 1);
        }

        return strtr(<<<'PHP'
<?php

{namespace}use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Snippet\Context\SnippetsFriendlyInterface;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class {className} implements ContextInterface, SnippetsFriendlyInterface
{
    /**
     * Initializes context. Every scenario gets it's own context object.
     *
     * @param array $parameters Suite parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }
}

PHP
            , array(
                '{namespace}' => $namespace,
                '{className}' => $classname,
            )
        );
    }

    /**
     * Returns priority of generator.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 0;
    }
}
