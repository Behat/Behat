<?php

$steps->Given('/I have entered (\d+) into the calculator/', function($num) use($world) {
    $world->nums[] = $num;
});

$steps->When('/I press add/', function() use($world) {
    $world->result = 0;
    foreach ($world->nums as $num) {
        $world->result += $num;
    }
    $world->nums = array();
});


$steps->Then('/the result should be (\d+) on the screen/', function($result) use($world) {
    assertEquals($result, $world->result);
});
