<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Snippet\ContextSnippet;
use Behat\Behat\Snippet\Snippet;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Context regex snippet generator.
 *
 * Generates regex snippets for regex-friendly contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextRegexSnippetGenerator extends AbstractContextSnippetGenerator
{
    /**
     * @var string[string]
     */
    private static $replacePatterns = array(
        "/(?<= |^)\\\'(?:((?!\\').)*)\\\'(?= |$)/" => "\\'([^\']*)\\'", // Single quoted strings
        '/(?<= |^)\"(?:[^\"]*)\"(?= |$)/'          => "\"([^\"]*)\"", // Double quoted strings
        '/(\d+)/'                                  => "(\\d+)", // Numbers
    );
    /**
     * @var string
     */
    private static $templateTemplate = <<<TPL
    /**
     * @%%s /^%s$/
     */
    public function %s(%s)
    {
        throw new PendingException();
    }
TPL;

    /**
     * Checks if generator supports environment and step.
     *
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return Boolean
     */
    public function supportsEnvironmentAndStep(Environment $environment, StepNode $step)
    {
        if (!$environment instanceof ContextEnvironment) {
            return false;
        }

        if (!$environment->hasContexts()) {
            return false;
        }

        return null !== $this->getMainContextClass($environment);
    }

    /**
     * Generates snippet from environment and step.
     *
     * @param ContextEnvironment $environment
     * @param StepNode           $step
     *
     * @return Snippet
     */
    public function generateSnippet(Environment $environment, StepNode $step)
    {
        $contextClass = $this->getMainContextClass($environment);

        $stepText = $step->getText();
        list($stepRegex, $tokenCount) = $this->getRegexAndTokenCount($stepText);
        $methodName = $this->getMethodName($contextClass, $stepText, $stepRegex);
        $methodArguments = $this->getMethodArguments($step, $tokenCount);
        $snippetTemplate = $this->getSnippetTemplate($stepRegex, $methodName, $methodArguments);

        return new ContextSnippet($step, $snippetTemplate, $contextClass);
    }

    /**
     * Generates snippet template using regex, method name and arguments.
     *
     * @param string   $regex
     * @param string   $methodName
     * @param string[] $methodArguments
     *
     * @return string
     */
    protected function getSnippetTemplate($regex, $methodName, array $methodArguments)
    {
        return sprintf(
            self::$templateTemplate,
            str_replace('%', '%%', $regex),
            $methodName,
            implode(', ', $methodArguments)
        );
    }

    /**
     * Returns replace patterns.
     *
     * @return array
     */
    protected function getReplacePatterns()
    {
        return array_keys(self::$replacePatterns);
    }

    /**
     * Tries to get main context class out of the environment.
     *
     * @param ContextEnvironment $environment
     *
     * @return null|string
     */
    private function getMainContextClass(ContextEnvironment $environment)
    {
        foreach ($environment->getContextClasses() as $class) {
            if (in_array(
                'Behat\Behat\Context\RegexAcceptingContext',
                class_implements($class)
            )
            ) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Generates definition regex and counts tokens inside.
     *
     * @param $stepText
     *
     * @return array
     */
    private function getRegexAndTokenCount($stepText)
    {
        $stepRegex = preg_replace(
            array_keys(self::$replacePatterns),
            array_values(self::$replacePatterns),
            $this->escapeStepText($stepText)
        );

        preg_match('/^' . $stepRegex . '$/', $stepText, $matches);

        return array($stepRegex, count($matches) ? count($matches) - 1 : 0);
    }

    /**
     * Returns escaped step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function escapeStepText($stepText)
    {
        return preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $stepText);
    }
}
