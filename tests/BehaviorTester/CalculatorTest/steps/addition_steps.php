<?php

$this

->  Given('/I have entered (\d+) into the calculator/', function($num) use ($t) {
        $t->numbers[] = $num;
    })

->  When('/I press add/', function() use($t) {
        $t->result = 0;
        array_walk($t->numbers, function($num) use($t) {
            $t->result += $num;
        });
        $t->numbers = array();
    })

->  When('/I press div/', function() use($t) {
        $t->result = array_shift($t->numbers);
        array_walk($t->numbers, function($num) use($t) {
            $t->result /= $num;
        });
        2/0;
        $t->numbers = array();
    })

->  Then('/the result should be (\d+) on the screen/', function($result) use($t) {
        $t->assertEquals($result, $t->result);
    })

;