<?php

function td(...$a) { foreach ($a as $v) var_dump($v); echo "\n--\ntd()\n"; die(3); }

function tp(...$a) { foreach ($a as $v) var_dump($v); echo "\n--\ntp()\n"; }
