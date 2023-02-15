#!/usr/bin/env pogo
<?php
#!require symfony/console: ~4.4

$io = new Symfony\Component\Console\Style\SymfonyStyle(
  new Symfony\Component\Console\Input\ArgvInput($argv),
  new Symfony\Component\Console\Output\ConsoleOutput()
);

$io->section('Lunch');
if ($io->confirm('Make a grilled cheese sandwich?')) {
  $io->writeln('We will need <comment>cheddar</comment> and <comment>bread</comment>');
}
else {
  $io->writeln('Too bad!');
}
