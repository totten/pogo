<?php

namespace Qp;

/**
 * Class QpInput
 * @package Qp
 *
 * Represents command-line input.
 *
 * Ex: 'do some -a --bee -c=123 --dee=456 thing -- extra'
 *   action: 'do'
 *   arguments: ['some', 'thing']
 *   options: ['a'=>TRUE,'bee'=>TRUE, 'c'=>123, 'dee'=>456]
 *   suffix: ['extra']
 */
class QpInput {

  /**
   * @var string
   *   The name of the current program being run.
   */
  public $program;

  /**
   * @var array
   *   Key-value pairs for each '--foo' or '-f' style option.
   *   If the option specifies a value, it is given here.
   *   Otherwise, the value defaults to TRUE.
   */
  public $options;

  /**
   * @var string
   *   The sub-action; the first non-optional
   */
  public $action;

  /**
   * @var array
   *   Any words which are not inputs
   */
  public $arguments;

  /**
   * @var array
   *   Any/all items which appear after the '--' separator
   */
  public $suffix;

  /**
   * @param array $args
   * @return static
   */
  public static function create($args) {
    return new static($args);
  }

  public function __construct($args = []) {
    $this->parse($args);
  }

  public function parse($args) {
    $this->program = $this->action = NULL;
    $this->options = $this->arguments = $this->suffix = [];
    $isSuffix = FALSE;

    $this->program = array_shift($args);
    foreach ($args as $arg) {
      if ($isSuffix) {
        $this->suffix[] = $arg;
      }
      elseif ($arg === '--') {
        $isSuffix = TRUE;
      }
      elseif (preg_match('/^--([^=]+)=(.*)$/', $arg, $m)) {
        $this->options[$m[1]] = $m[2];
      }
      elseif (preg_match('/^-([^=])=(.*)$/', $arg, $m)) {
        $this->options[$m[1]] = $m[2];
      }
      elseif (preg_match('/^--([^=]+)$/', $arg, $m)) {
        $this->options[$m[1]] = TRUE;
      }
      elseif (preg_match('/^-([a-zA-Z0-9])+$/', $arg, $m)) {
        for ($i = 0; $i < strlen($m[1]); $i++) {
          $this->options[$m[1]{$i}] = TRUE;
        }
      }
      elseif ($this->action === NULL) {
        $this->action = $arg;
      }
      else {
        $this->arguments[] = $arg;
      }
    }
  }

  /**
   * @param string|array $names
   * @return string
   */
  public function getOption($names, $default = NULL) {
    $names = (array) $names;
    foreach ($names as $name) {
      if (isset($this->options[$name])) {
        return $this->options[$name];
      }
    }
    return $default;
  }

}