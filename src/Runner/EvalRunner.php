<?php

namespace Pogo\Runner;

/**
 * Class DataRunner
 * @package Pogo\Runner
 *
 * Execute via 'eval(...cleanup($code)...)'
 *
 * Pro:
 *  - Supports pipes/cli arguments intuitively
 *  - Hides shebang
 *  - Works with pure-logic and with templates/interploated-text
 * Con:
 *  - Does not run the original file; weaker for xdebug and i/o
 */
class EvalRunner {

  /**
   * @param string $autoloader
   * @param string $script
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $script, $cliArgs) {
    $launcher = 'require_once getenv("POGO_AUTOLOAD");eval("?" . ">" . pogo_script());';

    $cmd = sprintf('POGO_SCRIPT=%s POGO_AUTOLOAD=%s php -r %s',
      escapeshellarg($script),
      escapeshellarg($autoloader),
      escapeshellarg($launcher)
    );
    $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    // printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}