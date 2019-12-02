<?php

namespace Pogo;

/**
 * Class PogoInput
 * @package Pogo
 *
 * Represents command-line input.
 *
 * A key issue is the DX-norm of calling an interpreter with
 * '#!/usr/bin/env my-interp'. This produces two levels of options.
 *
 * For example, suppose we have two programs:
 * - `/usr/local/bin/my-interp' which accepts argument `--interp-arg'
 * - `/home/me/myscript` which accepts argument `--script-arg`
 *
 * Inside of `myscript`, you can use a declaration like:
 *
 * #!/usr/bin/env my-interp --interp-arg
 *
 * When the user calls `./myscript --script-arg`, it reads the `#!`, and
 * the full command becomes:
 *
 * /usr/local/bin/my-interp --interp-arg ./myscript --script-arg
 *
 * Observe that file-name `./myscript` is a demarcation point - before that, all
 * args should go to the interpreter. After that, all args should go to
 * the script.
 */
class PogoInput {

  /**
   * Convert from Pogo argv to Symfony argv. Key differences:
   *  - Pogo takes actions with `--`. Symfony omits the `--`.
   *  - Pogo commands only accept one *argument*, i.e. *the target script*.
   *  - Everything after the script will be treated as *arguments*, not as *options*.
   *  - If there is a script, the default action is 'run'. If no script, then 'help.
   *
   * @param array $args
   *   Argv input, as expected by Pogo
   * @return array
   *   Argv input, as expected by Symfony Console
   */
  public static function filter($args) {
    // Usage: pogo [<action>] [action-options] [--] <script-file> [script-options]

    $actionRegex = '/^--(run|get|parse|up)(=.*)?$/';

    $interpreter = NULL;
    $action = NULL;
    $script = NULL;
    $result = [];
    $isScriptArg = FALSE;

    foreach ($args as $arg) {
      $isOpt = $arg{0} === '-';
      if ($isScriptArg) {
        $result[] = $arg;
      }
      elseif ($interpreter === NULL) {
        $interpreter = $arg;
        $result[] = $interpreter;
      }
      elseif ($action === NULL && preg_match($actionRegex, $arg, $m)) {
        $action = $m[1];
      }
      elseif ($arg === '--') {
        $isScriptArg = TRUE;
        $result[] = $arg;
      }
      elseif ($script === NULL && !$isOpt) {
        $script = $arg;
        if (empty($action)) {
          $action = 'run';
        }
        if (!in_array('--', $args)) {
          $result[] = '--';
        }
        $result[] = $script;
        $isScriptArg = TRUE;
      }
      else {
        $result[] = $arg;
      }
    }

    if (empty($action)) {
      $action = 'help';
    }
    array_splice($result, 1, 0, [$action]);

    return $result;
  }

}
