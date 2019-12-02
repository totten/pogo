#!/usr/bin/env pogo
<?php

## This example uses the "Silly" (https://github.com/mnapoli/silly) CLI framework.
## It provides a very pithy way to declare and parse CLI arguments.

#!require { mnapoli/silly: ~1.7, php: '>=7.0' }
use Symfony\Component\Console\Style\SymfonyStyle;

$app = new Silly\Application();
$app->command('greet [name] [--yell]', function ($name, $yell, SymfonyStyle $io) {
  if (empty($name)) {
    $name = $io->ask('What\'s my name again? ');
  }

  $text = 'Hello, '.$name;

  if ($yell) {
    $text = strtoupper($text);
  }

  $io->writeln($text);
});
$app->run();
