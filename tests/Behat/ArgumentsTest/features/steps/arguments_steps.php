<?php

$steps->When('/^I enter a string$/', function($string) use($world) {
    $world->string = $string;
});

$steps->Then('/^String must be$/', function($string) use($world) {
    assertEquals($string, $world->string);
});

$steps->When('/^I enter a table$/', function($table) use($world) {
    $world->table = $table;
});

$steps->Then('/^Table must be$/', function($table) use($world) {
    assertEquals($table, $world->table);
});
