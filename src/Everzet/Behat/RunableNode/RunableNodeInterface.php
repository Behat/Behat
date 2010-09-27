<?php

namespace Everzet\Behat\RunableNode;

interface RunableNodeInterface
{
    const PASSED    = 0;
    const SKIPPED   = 1;
    const PENDING   = 2;
    const UNDEFINED = 3;
    const FAILED    = 4;

    public function getResult();
}
