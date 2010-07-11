<?php

$steps

->  Given('/I have entered (\d+) into the calculator/', function($num) {
#        throw new \Exception('Error processing request');
    })

->  When('/I press add/', function() {

    })

#->  When('/I press d[iI]v/', function() {
#
#    })

->  When('/I press div/', function() {

    })

->  Then('/the result should be (\d+) on the screen/', function($result) {

    })

;