<?php

namespace Everzet\Gherkin\Node;

interface NodeVisitorInterface
{
    public function visit($visitee);
}
