#!/usr/bin/env pogo
<?php

## This example uses the "Robo" (https://robo.li/) CLI framework.
## It provides a handy way to script "tasks".

#!require "henrikbjorn/lurker": "^1.2"
#!require "consolidation/robo": "~1"

class RoboFile extends \Robo\Tasks {
  // Define the "watch" task.
  function watch() {
    $this->taskWatch()->monitor('hello.txt', function () {
      $this->say('hello.txt was changed!');
    })->run();
  }
}

exit((new \Robo\Runner())->execute($_SERVER['argv']));
