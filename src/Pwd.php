<?php
namespace Pogo;

class Pwd {

  public static function getPwd() {
    if (getenv('PWD')) {
      // Symlink sensitivity training
      return getenv('PWD');
    }
    else {
      throw new \RuntimeExcpetion('Failed to determine current working directory.');
    }
  }

}
