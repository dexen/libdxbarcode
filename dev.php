<?php

function td(...$a) { foreach ($a as $v) var_dump($v); echo "\n--\ntd()\n"; die(3); }
