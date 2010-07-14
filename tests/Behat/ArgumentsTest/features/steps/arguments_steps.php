<?php

$steps->Then('/^String must be$/', function($arg1) use($world) {
    assertEquals('   a string
  with something
be
a
u
  ti
    ful', $arg1);
});
