<?php

namespace Behat\Config\Converter;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

class UsedClassesCollector extends NodeVisitorAbstract
{
    /**
     * @var array <string, class-string>
     */
    private array $usedClasses = [];

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FullyQualified) {
            return $this->importClass($node);
        }

        if ($node instanceof ClassConstFetch && $node->class instanceof Name) {
            // E.g. a `MyClass::class` argument
            return new ClassConstFetch(
                $this->importClass($node->class),
                $node->name,
                $node->getAttributes(),
            );
        }

        return null;
    }

    private function importClass(Name $node): Name
    {
        $className = $node->toString();
        $shortName = $node->getLast();

        $this->usedClasses[$shortName] = $className;

        return new Name($shortName);
    }

    /**
     * @return array <string, class-string>
     */
    public function getUsedClasses()
    {
        return $this->usedClasses;
    }
}
