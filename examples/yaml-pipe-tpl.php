#!/usr/bin/env pogo
<?php

## This example is decent smoke-test - it relies on several things
## - Uses multiple ?php directives
## - Uses STDIN and $argv
## - Uses a third-party library
## - Can be executed/tested with just a little bash scripting
##
## Usage: echo '{name: Alice, color: cyan}' | pogo yaml-pipe-tpl.php foo bar

#!require symfony/yaml: ~3.0
$parsed = Symfony\Component\Yaml\Yaml::parse(file_get_contents(pogo_stdin()));
?>
Hello, <?php echo $parsed['name']; ?>!!
I hope you like the color <?php echo $parsed['color']; ?>.
Args: <?php $d = $argv; array_shift($d); echo json_encode($d); ?>