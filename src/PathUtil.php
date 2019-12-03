<?php
namespace Pogo;

class PathUtil {

  public static $SEP = DIRECTORY_SEPARATOR;

  public static function getPwd() {
    if (getenv('PWD')) {
      // Symlink sensitivity training
      return getenv('PWD');
    }
    else {
      throw new \RuntimeExcpetion('Failed to determine current working directory.');
    }
  }

  /**
   * Determine full path to an external command (by searching PATH).
   *
   * @param string $name
   * @return NULL|string
   */
  public static function findCommand($name) {
    $paths = explode(PATH_SEPARATOR, getenv('PATH'));
    foreach ($paths as $path) {
      if (file_exists("$path/$name")) {
        return "$path/$name";
      }
    }
    return NULL;
  }

  /**
   * Convert relative paths to absolute
   *
   * @param $expr
   * @param string|NULL $base
   * @return string
   */
  public static function makeAbsolute($expr, $base = NULL) {
    if (self::isAbsolute($expr)) {
      return $expr;
    }

    if ($base === NULL) {
      $base = self::getPwd();
    }

    $base = rtrim($base, '/' . self::$SEP);
    return $base . self::$SEP . $expr;
  }

  public static function isAbsolute($expr) {
    $slashes = '/' . self::$SEP;
    if (strpos($slashes, $expr[0]) !== FALSE) {
      return TRUE;
    }
    if (self::$SEP === '\\' && preg_match(';^[a-zA-Z]:[\\/];', $expr)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Evaluate as many expressions like "/./" or "/../" as we can.
   *
   * If the path is relative, then the leading "../" may not be evaluated.
   *
   * @param string $path
   * @return string
   */
  public static function evaluateDots($path) {
    $q = preg_quote(self::$SEP, ';');

    $path = self::cleanSlashes($path);
    $hasTail = substr($path, -1) === self::$SEP ? self::$SEP : '';
    if (!$hasTail) {
      $path .= self::$SEP;
    }

    $path = preg_replace(";^(\.{$q})+(.);", '$2', $path);
    while (preg_match(";{$q}\.\.?{$q};", $path)) {
      $path = preg_replace(";{$q}\.{$q};", self::$SEP, $path);
      $path = preg_replace(";{$q}[^{$q}]+{$q}\.\.{$q};", self::$SEP, $path);
    }
    return $hasTail ? $path : rtrim($path, self::$SEP);
  }

  /**
   * @param $path
   * @param $preferredSeparator
   * @return array
   */
  public static function cleanSlashes($path) {
    if (self::$SEP !== '/') {
      $path = str_replace('/', self::$SEP, $path);
    }

    $q = preg_quote(self::$SEP, ';');
    $path = preg_replace(";{$q}+;", self::$SEP, $path);
    return $path;
  }

}
