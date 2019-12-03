<?php
namespace Pogo;

class PathUtil {

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

}
