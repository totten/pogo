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
   * @var string
   *   One of: 'auto', 'include', 'dash-b', etc
   */
  public $runMode = 'auto';

  /**
   * Return first doc comment found in this file.
   *
   * @param string $file
   *   PHP file to parse
   * @return static
   */
  public static function parse($file) {
    $code = file_get_contents($file);

    $pragmas = array_filter(
      token_get_all($code),
      function ($entry) {
        return $entry[0] == T_COMMENT
        && preg_match(';#!;', $entry[1]);
      });

    $metadata = new static();
    $metadata->file = $file;
    foreach ($pragmas as $pragma) {
      if (preg_match(';#!\s*require \s*(.*)$;', $pragma[1], $m)) {
        $yaml = Yaml::parse(trim($m[1]));
        $metadata->require = array_merge($metadata->require, $yaml);
      }
      elseif (preg_match(';#!\s*ttl \s*(\d+\s+(sec|min|hour|day|week|month|year)s?)$;', $pragma[1], $m)) {
        $metadata->ttl = trim($m[1]);
      }
      elseif (preg_match(';#!\s*run \s*([a-zA-Z0-9\-_]+)\s*$;', $pragma[1], $m)) {
        $metadata->runMode = $m[1];
      }
      elseif (preg_match(';#!\s*ini (.*)$;', $pragma[1], $m)) {
        $yaml = Yaml::parse(trim($m[1]));
        $metadata->ini = array_merge($metadata->ini, $yaml);
      }
      else {
        self::warn(sprintf("Unrecognized pragma \"%s\" on line %d of %s", trim($pragma[1]), $pragma[2], $file));
      }
    }

    ksort($metadata->require);

    return $metadata;
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

}
