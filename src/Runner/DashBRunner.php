<?php

namespace Pogo\Runner;

/**
 * Class DashBRunner
 * @package Pogo\Runner
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
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $scriptMetadata, $cliArgs) {
    $script = $scriptMetadata->file;
    // `php -B ... -F ...` will loop through every line of input; we coerce
    // this to prevent multiple invocations

    // WISHLIST: When buffering, use FIFO instead of regular temp file.
    // WISHLIST: Probe input (non-blocking read) to decide whether to buffer.

    // TODO Maybe use this? http://docs.php.net/manual/en/function.posix-isatty.php

    $doAutoload = sprintf('require_once %s;', var_export($autoloader, 1));
    if (empty($scriptMetadata->runner['buffer'])) {
      $cmd = sprintf('echo | POGO_SCRIPT=%s php -B %s -F %s', escapeshellarg($script), escapeshellarg($doAutoload), escapeshellarg($script));
    }
    else {
      $buffer = tempnam(sys_get_temp_dir(), 'pogo-buffer');
      register_shutdown_function(function() use ($buffer) {
        if (file_exists($buffer)) {
          @unlink($buffer);
        }
      });
      file_put_contents($buffer, file_get_contents('php://stdin'));
      $cmd = sprintf('echo | POGO_STDIN=%s php -B %s -F %s', escapeshellarg($buffer), escapeshellarg($doAutoload), escapeshellarg($script));
    }

    $cmd .= \Pogo\Php::iniToArgv($scriptMetadata->ini);
    $cmd .= ' -- ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    // printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}
