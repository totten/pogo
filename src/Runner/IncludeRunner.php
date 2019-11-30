<?php

namespace Qp\Runner;

/**
 * Class IncludeRunner
 * @package Qp\Runner
 *
 * Execute via 'php -r 'require $autoload; include $script;'
 *
 * Pro:
 *  - Executes the original file; good for xdebug and i/o
 *  - Supports pipes/cli arguments intuitively
 *  - Works with pure-logic and with templates/interploated-text
 * Con:
 *  - If file has a shebang, it's displayed as output
 */
class IncludeRunner {

  /**
   * @param string $autoloader
   * @param string $script
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $script, $cliArgs) {
    $launcher = 'require_once getenv("QP_AUTOLOAD");include getenv("QP_SCRIPT");';

    $cmd = sprintf('QP_SCRIPT=%s QP_AUTOLOAD=%s php -r %s',
      escapeshellarg($script),
      escapeshellarg($autoloader),
      escapeshellarg($launcher)
    );
    $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}