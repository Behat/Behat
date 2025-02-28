<?php

namespace Behat\Config\Converter;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class UseStatementAdder extends NodeVisitorAbstract
{
    /**
     * @var array <string, class-string>
     */
    private array $uses = [];

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Name\FullyQualified) {
            $className = $node->toString();
            $shortName = $node->getLast();

            $this->uses[$shortName] = $className;

            return new Node\Name($shortName);
        }
        return null;
    }

    /**
     * @return array <string, class-string>
     */
    public function getUseStatements()
    {
        return $this->uses;
    }
}
