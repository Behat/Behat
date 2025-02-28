<?php

namespace Behat\Config\Converter;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;

class CustomPrettyPrinter extends Standard
{
    protected function pExpr_Array(Array_ $node): string
    {
        $printedArray = '[';
        $printedArray .= $this->pCommaSeparatedMultiline($node->items, true);

        return $printedArray . ($this->nl . ']');
    }

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        $result =  $this->pDereferenceLhs($node->var);
        $this->indent();
        $result .=  $this->nl . '->' . $this->pObjectProperty($node->name) . '(';
        if (count($node->args) > 1) {
            $result .= $this->pCommaSeparatedMultiline($node->args, false) . $this->nl;
        } else {
            $result .= $this->pCommaSeparated($node->args);
        }
        $result .= ')';
        $this->outdent();
        return $result;
    }
}
