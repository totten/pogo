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
 *
 * Ex: '/usr/bin/pogo --get -a --bee -c=123 --dee=456 myfile --extra'
 *   interpreter: '/usr/bin/pogo'
 *   interpreterOptions: ['a'=>TRUE,'bee'=>TRUE, 'c'=>123, 'dee'=>456]
 *   action: 'get'
 *   script: 'myfile'
 *   scriptArgs: ['--extra']
 */
class PogoInput {

  /**
   * @var string
   *   The name of the current program being run.
   */
  public $interpreter;

  /**
   * @var array
   *   Key-value pairs for each '--foo' or '-f' style option.
   *   If the option specifies a value, it is given here.
   *   Otherwise, the value defaults to TRUE.
   */
  public $interpreterOptions;

  /**
   * @var string
   *   The sub-action; the first non-optional
   */
  public $action;

  /**
   * @var string
   *   The PHP file to scan/execute.
   */
  public $script;

  /**
   * @var array
   *   Any/all items being passed to the downstream script.
   */
  public $scriptArgs;

  /**
   * @param array $args
   * @return static
   */
  public static function create($args) {
    $actions = ['run', 'get', 'parse', 'up', 'help'];
    return new static($args, $actions, $actions);
  }

  public function __construct($args = [], $actions = []) {
    $this->parse($args, $actions);
  }

  public function parse($args, $actions) {
    $this->interpreter = $this->action = $this->script = NULL;
    $this->interpreterOptions = $this->scriptArgs = [];
    $isScriptArg = FALSE;

    $this->interpreter = array_shift($args);
    foreach ($args as $arg) {
      if ($isScriptArg) {
        $this->scriptArgs[] = $arg;
      }
      elseif ('--' === $arg) {
        $isScriptArg = TRUE;
      }
      elseif (preg_match('/^--([^=]+)=(.*)$/', $arg, $m)) {
        $this->interpreterOptions[$m[1]] = $m[2];
      }
      elseif (preg_match('/^-([^=])=(.*)$/', $arg, $m)) {
        $this->interpreterOptions[$m[1]] = $m[2];
      }
      elseif (preg_match('/^--([^=]+)$/', $arg, $m)) {
        if (in_array($m[1], $actions)) {
          if ($this->action === NULL) {
            $this->action = $m[1];
          }
          else {
            throw new \Exception("Too many actions specified: {$this->action} " . $m[1]);
          }
        }
        else {
          $this->interpreterOptions[$m[1]] = TRUE;
        }
      }
      elseif (preg_match('/^-([a-zA-Z0-9])+$/', $arg, $m)) {
        for ($i = 0; $i < strlen($m[1]); $i++) {
          $this->interpreterOptions[$m[1]{$i}] = TRUE;
        }
      }
      elseif ($this->script === NULL) {
        $this->script = $arg;
        $isScriptArg = TRUE;
      }
      else {
        throw new \Exception("Failed to parse argument: $arg");
      }
    }
  }

  /**
   * @param string|array $names
   *   List of option-names to check. The first extant one will be returned.
   * @param string|NULL $default
   *   The value to return if none of the `$names` are defined.
   * @return string
   */
  public function getOption($names, $default = NULL) {
    $names = (array) $names;
    foreach ($names as $name) {
      if (isset($this->interpreterOptions[$name])) {
        return $this->interpreterOptions[$name];
      }
    }
    return $default;
  }

}
