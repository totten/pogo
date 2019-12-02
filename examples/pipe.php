#!/usr/bin/env pogo
<?php

## This example reads from STDIN and $argv -- and prints them back out.
##
## Note that you can generally just read `php://stdin` per normal, but
## `pogo_stdin()` enables support for some experimental runners.

$data = file_get_contents(pogo_stdin());
printf("Received arguments:\n%s\n", json_encode($argv, JSON_PRETTY_PRINT));
printf("Received input %s:\n%s\n", md5($data), $data);
