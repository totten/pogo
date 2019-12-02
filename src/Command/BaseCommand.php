<?php

namespace Pogo\Command;

use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command {

  private $synopsis = [];

  /**
   * Returns the synopsis for the command.
   *
   * @param bool $short Whether to show the short version of the synopsis (with options folded) or not
   *
   * @return string The synopsis
   */
  public function getSynopsis($short = FALSE) {
    $key = $short ? 'short' : 'long';

    if (!isset($this->synopsis[$key])) {
      global $argv;
      $prog = basename($argv[0]);
      $this->synopsis[$key] = trim(sprintf('%s --%s %s', $prog, $this->getName(), $this->getDefinition()->getSynopsis($short)));
    }

    return $this->synopsis[$key];
  }

}
