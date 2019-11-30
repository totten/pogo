<?php

namespace Qp\Runner;

/**
 * Class DashBRunner
 * @package Qp\Runner
 *
 * Execute via 'php -B requireAutoLoader -F theScript.php'
 *
 * Pro:
 *  - Executes the original file; good for xdebug and i/o
 *  - Hides shebang
 *  - Works with pure-logic and with templates/interploated-text
 * Con:
 *  - Cannot pass through pipes effectively
 */
class DashBRunner {

  /**
   * @param string $autoloader
   * @param string $script
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $script, $cliArgs) {
    // `php -B ... -F ...` will loop through every line of input; we coerce
    // this to prevent multiple invocations
    $doAutoload = sprintf('require_once %s;', var_export($autoloader, 1));
    $cmd = sprintf('echo | php -B %s -F %s', escapeshellarg($doAutoload), escapeshellarg($script));

    $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    // printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}