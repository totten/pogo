<?php

namespace Pogo;

class Php {

  /**
   * Convert a list of PHP INI values to a CLI arg list.
   *
   * @param array $ini
   *   Ex: ['memory_limit'=>'256m']
   * @return string
   *   Ex: '-d memory_limit=256m'
   */
  public static function iniToArgv($ini) {
    $buf = '';
    foreach ($ini as $k => $v) {
      $buf .= ' -d ' . escapeshellarg("$k=$v");
    }
    return $buf;
  }

  /**
   * Convert a list of PHP INI values to PHP code.
   *
   * @param array $ini
   *   Ex: ['memory_limit'=>'256m']
   * @return string
   *   Ex: 'ini_set("memory_limit", "256m");'
   */
  public static function iniToCode($ini) {
    $buf = '';
    foreach ($ini as $k => $v) {
      $buf .= sprintf("ini_set(%s, %s);\n", var_export($k, TRUE), var_export($v, TRUE));
    }
    return $buf;
  }

  /**
   * @param array $ini
   *   Ex: ['memory_limit'=>'256m']
   */
  public static function applyIni($ini) {
    if (empty($ini)) {
      // OK
      return;
    }

    // FIXME: we could actually do ini_set() for some directives, but should
    // still warn about unsupported directives.
    fwrite(STDERR, "WARNING: EvalRunner does not implement ini support");
  }

}
