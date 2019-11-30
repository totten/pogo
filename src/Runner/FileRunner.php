<?php

namespace Qp\Runner;

/**
 * Class FileRunner
 * @package Qp\Runner
 *
 * Make a filtered copy of the script and execute it
 *
 * Pro:
 *  - Supports pipes/cli arguments intuitively
 *  - Works with pure-logic and with templates/interploated-text
 *  - Hides shebang
 * Con:
 *  - Does not run the original file; weaker for xdebug and i/o
 */
class FileRunner {

  /**
   * @param string $autoloader
   * @param string $script
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $script, $cliArgs) {
    throw new \Exception('Not implemented: FileRunner');
//    $code = file_get_contents($script);
//    $launcher = trim(preg_replace('/^\s*<' . '\?php/', '', $launcher, 1));
//
//    $cmd = sprintf('QP_SCRIPT=%s QP_AUTOLOAD=%s php %s -r %s',
//      escapeshellarg($script),
//      escapeshellarg($autoloader),
//      $defines,
//      escapeshellarg($launcher)
//    );
//    $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
//    printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
//    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
//    return proc_close($process);
  }

}