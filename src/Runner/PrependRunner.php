<?php
namespace Pogo\Runner;

/**
 * Class AutoPrependRunner
 * @package Pogo\Runner
 *
 * Execute via 'php -d auto_prepend_file=$autoload $script;'
 */
class PrependRunner {

  /**
   * @param string $autoloader
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $scriptMetadata, $cliArgs) {
    $script = $scriptMetadata->file;

    $defines = \Pogo\Php::iniToArgv($scriptMetadata->ini + ['auto_prepend_file' => $autoloader]);
    $cmd = sprintf('POGO_SCRIPT=%s php %s %s', escapeshellarg($script), $defines, escapeshellarg($script));

    if ($cliArgs) {
      $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $cliArgs));
    }
    // printf("[%s] Running command: $cmd\n", __CLASS__, $cmd);
    $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
    return proc_close($process);
  }

}
