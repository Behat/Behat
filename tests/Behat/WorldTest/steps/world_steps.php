<?php

#$steps->Given('/I have entered (\d+)/', function($num) use($world) {
#    assertNull($world->value);
#    $world->value = $num;
#});
#
#$steps->Then('/I must have (\d+)/', function($num) use($world) {
#    assertEquals($num, $world->value);
#});
#
#$steps->When('/I add (\d+)/', function($num) use($world) {
#    $world->value += $num;
#});
