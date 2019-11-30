#!/usr/bin/env pogo
<?php
#!run {with: dash-b, buffer: true}
$data = file_get_contents(pogo_stdin());
printf("Received arguments:\n%s\n", json_encode($argv, JSON_PRETTY_PRINT));
printf("Received input %s:\n%s\n", md5($data), $data);
