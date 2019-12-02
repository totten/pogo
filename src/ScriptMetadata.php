<?php

namespace Pogo;

use Symfony\Component\Yaml\Yaml;

class ScriptMetadata {

  /**
   * @var string
   */
  public $file = NULL;

  /**
   * @var array
   *   Ex: ['allow_url_include'=>1]
   */
  public $ini = [];

  /**
   * @var array
   *   List of required packages
   *   Ex: ['symfony/yaml' => '~3.0`]
   */
  public $require = [];

  /**
   * @var string
   *   How long to keep the current dependencies.
   *   Ex: "1 day", "2 months", "180 sec"
   * @see strtotime
   */
  public $ttl = '7 days';

  /**
   * @var array
   *   A list of properties describing the runner.
   *   - with: string, e.g. 'auto' or 'include' or 'eval'
   *
   *   Additional, per-runner properties may be added.
   */
  public $runner = ['with' => 'auto'];

  /**
   * Return first doc comment found in this file.
   *
   * @param string $file
   *   PHP file to parse
   * @return static
   */
  public static function parse($file) {
    $metadata = new static();
    $metadata->file = $file;
    $metadata->parseCode(file_get_contents($file), $file);
    return $metadata;
  }

  public function parseCode($code, $fileName = '') {
    $pragmas = array_filter(
      token_get_all($code),
      function ($entry) {
        return $entry[0] == T_COMMENT
        && preg_match(';#!;', $entry[1]);
      });

    foreach ($pragmas as $pragma) {
      if (preg_match(';#!\s*require \s*(.*)$;', $pragma[1], $m)) {
        $yaml = Yaml::parse(trim($m[1]));
        if (is_array($yaml)) {
          $this->require = array_merge($this->require, $yaml);
        }
        else {
          self::error(sprintf("Malformed pragma \"%s\" on line %d of %s", trim($pragma[1]), $pragma[2], $fileName));
        }
      }
      elseif (preg_match(';#!\s*ttl \s*(\d+\s+(sec|min|hour|day|week|month|year)s?)$;', $pragma[1], $m)) {
        $this->ttl = trim($m[1]);
      }
      elseif (preg_match(';#!\s*run \s*(.*)$;', $pragma[1], $m)) {
        $yaml = Yaml::parse(trim($m[1]));
        if (is_string($yaml)) {
          $this->runner = ['with' => $yaml];
        }
        else {
          $this->runner = $yaml;
        }
      }
      elseif (preg_match(';#!\s*ini (.*)$;', $pragma[1], $m)) {
        $yaml = Yaml::parse(trim($m[1]));
        $this->ini = array_merge($this->ini, $yaml);
      }
      else {
        self::warn(sprintf("Unrecognized pragma \"%s\" on line %d of %s", trim($pragma[1]), $pragma[2], $fileName));
      }
    }

    ksort($this->require);
    return $this;
  }

  public function getDigest() {
    ksort($this->require);
    return hash('sha1', serialize($this->require));
  }

  /**
   * @param $msg
   */
  private static function warn($msg) {
    // trigger_error($msg, E_USER_NOTICE);
    fwrite(STDERR, "WARNING: $msg\n");
  }

  /**
   * @param $msg
   */
  private static function error($msg) {
    // trigger_error($msg, E_USER_ERROR);
    // fwrite(STDERR, "ERROR: $msg\n");
    // exit(1);
    throw new \Exception($msg);
  }

}
