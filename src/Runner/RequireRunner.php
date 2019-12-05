<?php

namespace Pogo\Runner;

/**
 * Class IncludeRunner
 * @package Pogo\Runner
 *
 * Execute via 'require $script'
 *
 * Pro:
 *  - Executes the original file; good for xdebug and i/o
 *  - Supports pipes/cli arguments intuitively
 *  - Works with pure-logic and with templates/interploated-text
 * Con:
 *  - If file has a shebang, it's displayed as output
 */
class RequireRunner {

  /**
   * @param string $autoloader
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @param array $cliArgs
   * @return int
   */
  public function run($autoloader, $scriptMetadata, $cliArgs) {
    require_once $autoloader;

    \Pogo\Php::applyIni($scriptMetadata->ini);

    putenv('POGO_SCRIPT=' . $scriptMetadata->file);
    putenv('POGO_AUTOLOAD=' . $autoloader);
    global $argv;
    $oldArgv = $argv;
    $argv = array_merge([$scriptMetadata->file], $cliArgs);
    require $scriptMetadata->file;
    $argv = $oldArgv;

    // FIXME: how to detect exit code?
    return 0;
  }

}
