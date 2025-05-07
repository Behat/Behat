<?php

namespace Behat\Config\Converter;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class UsedClassesCollector extends NodeVisitorAbstract
{
    /**
     * @var array <string, class-string>
     */
    private array $usedClasses = [];

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Name\FullyQualified) {
            return $this->importClass($node);
        }

        if ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name) {
            // E.g. a `MyClass::class` argument
            return new Node\Expr\ClassConstFetch(
                $this->importClass($node->class),
                $node->name,
                $node->getAttributes(),
            );
        }

        return null;
    }

    private function importClass(Node\Name $node): Node\Name
    {
        $className = $node->toString();
        $shortName = $node->getLast();

        $this->usedClasses[$shortName] = $className;

        return new Node\Name($shortName);
    }

    /**
     * @return array <string, class-string>
     */
    public function getUsedClasses()
    {
        return $this->usedClasses;
    }
}
