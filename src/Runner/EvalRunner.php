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
 * Con:
 *  - Does not run the original file; weaker for xdebug and i/o
 */
class EvalRunner {

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
    $argv = $_SERVER['argv'] = array_merge([$scriptMetadata->file], $cliArgs);
    // call_user_func() - Bypass php-scoper
    $code = "?" . ">" . call_user_func('\pogo_script');
    eval($code);
    $argv = $_SERVER['argv'] = $oldArgv;

    // FIXME: how to detect exit code?
    return 0;
  }

}
