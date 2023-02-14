#!/usr/bin/env pogo
<?php

## This example uses the "clippy" (https://github.com/clippy/std) CLI framework.
## It is a variant of "silly" that inherits the pithy notation for CLI arguments,
## but it formalizes a plugin mechanism for the service-container.

#!require clippy/std: ~0.4.3
namespace Clippy;
use Symfony\Component\Console\Style\SymfonyStyle;

$c = clippy()->register(plugins());
$c['app']->command('greet [name] [--yell]', function ($name, $yell, SymfonyStyle $io) {
  if (empty($name)) {
    $name = $io->ask('What\'s my name again? ');
  }

  $text = 'Hello, '.$name;

  if ($yell) {
    $text = strtoupper($text);
  }

  $io->writeln($text);
});
$c['app']->run();
