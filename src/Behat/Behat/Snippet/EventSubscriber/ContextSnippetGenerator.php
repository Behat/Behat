<?php

namespace Behat\Behat\Snippet\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Snippet\ContextSnippet;
use Behat\Behat\Snippet\Event\SnippetCarrierEvent;
use Behat\Behat\Snippet\Util\Transliterator;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context snippet generator.
 * Generates snippets for non-empty context pools.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextSnippetGenerator implements EventSubscriberInterface
{
    /**
     * @var string[string]
     */
    private static $proposedMethods = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::CREATE_SNIPPET => array('createSnippet', 0));
    }

    /**
     * Generate snippet and set it to the event.
     *
     * @param SnippetCarrierEvent $event
     */
    public function createSnippet(SnippetCarrierEvent $event)
    {
        if ($event->hasSnippet()) {
            return;
        }
        if (!$event->getContextPool()->hasContexts()) {
            return;
        }

        $context = $event->getContextPool()->getFirstContext();
        $step = $event->getStep();

        $reflection = new ReflectionClass(is_object($context) ? get_class($context) : $context);
        $contextClass = $reflection->getName();
        $replacePatterns = array(
            "/(?<= |^)\\\'(?:((?!\\').)*)\\\'(?= |$)/", // Single quoted strings
            '/(?<= |^)\"(?:[^\"]*)\"(?= |$)/', // Double quoted strings
            '/(\d+)/', // Numbers
        );

        $text = $step->getText();
        $text = preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $text);
        $regex = preg_replace(
            $replacePatterns,
            array(
                "\\'([^\']*)\\'",
                "\"([^\"]*)\"",
                "(\\d+)",
            ),
            $text
        );

        preg_match('/' . $regex . '/', $step->getText(), $matches);
        $count = count($matches) - 1;

        $methodName = preg_replace($replacePatterns, '', $text);
        $methodName = Transliterator::transliterate($methodName, ' ');
        $methodName = preg_replace('/[^a-zA-Z\_\ ]/', '', $methodName);
        $methodName = str_replace(' ', '', ucwords($methodName));

        if (0 !== strlen($methodName)) {
            $methodName[0] = strtolower($methodName[0]);
        } else {
            $methodName = 'stepDefinition1';
        }

        // get method number from method name
        $methodNumber = 2;
        if (preg_match('/(\d+)$/', $methodName, $matches)) {
            $methodNumber = intval($matches[1]);
        }

        // check that proposed method name isn't arelady defined in context
        while ($reflection->hasMethod($methodName)) {
            $methodName = preg_replace('/\d+$/', '', $methodName);
            $methodName .= $methodNumber++;
        }

        // check that proposed method name haven't been proposed earlier
        if (isset(self::$proposedMethods[$contextClass])) {
            foreach (self::$proposedMethods[$contextClass] as $proposedRegex => $proposedMethod) {
                if ($proposedRegex !== $regex) {
                    while ($proposedMethod === $methodName) {
                        $methodName = preg_replace('/\d+$/', '', $methodName);
                        $methodName .= $methodNumber++;
                    }
                }
            }
        }
        self::$proposedMethods[$contextClass][$regex] = $methodName;

        $args = array();
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg" . ($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyStringNode) {
                $args[] = "PyStringNode \$string";
            } elseif ($argument instanceof TableNode) {
                $args[] = "TableNode \$table";
            }
        }

        $description = $this->generateSnippet($regex, $methodName, $args);

        $event->setSnippet(new ContextSnippet($step->getType(), $description, array($contextClass)));
    }

    protected function generateSnippet($regex, $methodName, array $args)
    {
        return sprintf(
            <<<PHP
    /**
     * @%s /^%s$/
     */
    public function %s(%s)
    {
        throw new PendingException();
    }
PHP
            ,
            '%s',
            str_replace('%', '%%', $regex),
            $methodName,
            implode(', ', $args)
        );
    }
}
