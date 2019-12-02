#!/usr/bin/env pogo
<?php
## This example uses several ?php directives to show that it works with templates.
#!run {with: dash-b, buffer: true}
#!require symfony/yaml: ~3.0
$parsed = Symfony\Component\Yaml\Yaml::parse(file_get_contents(pogo_stdin()));
?>
Hello, <?php echo $parsed['name']; ?>!!
I hope you like the color <?php echo $parsed['color']; ?>.
Args: <?php $d = $argv; array_shift($d); echo json_encode($d); ?>