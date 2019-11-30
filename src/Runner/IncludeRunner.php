<?php

namespace Pogo\Runner;

/**
 * Class IncludeRunner
 * @package Pogo\Runner
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
   * @param ScriptMetadata $scriptMetadata
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $scriptMetadata, $cliArgs) {
    $script = $scriptMetadata->file;
    $launcher = 'require_once getenv("POGO_AUTOLOAD");include getenv("POGO_SCRIPT");';

    $cmd = sprintf('POGO_SCRIPT=%s POGO_AUTOLOAD=%s php -r %s',
      escapeshellarg($script),
      escapeshellarg($autoloader),
      escapeshellarg($launcher)
    );

    $cmd .= \Pogo\Php::iniToArgv($scriptMetadata->ini);
    $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    // printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}
