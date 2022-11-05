<?php
namespace Pogo;

use Symfony\Component\Filesystem\Filesystem;

class FilteredDirectoryIterator extends \FilterIterator {

  protected $basedir;

  /**
   * @var callable
   *   function(string $relPath, SplFileInfo $file): bool;
   */
  protected $filter;

  public function __construct(string $basedir, callable $filter) {
    $this->basedir = $basedir;
    $this->filter = $filter;
    $dirIter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basedir,
      \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
    ));
    parent::__construct($dirIter);
  }

  public function accept(): bool {
    $fs = new Filesystem();
    $current = $this->current();
    $fullPath = $current->getPath() . DIRECTORY_SEPARATOR . $current->getFilename();
    $relPath = rtrim($fs->makePathRelative($fullPath, $this->basedir), DIRECTORY_SEPARATOR);
    return call_user_func($this->filter, $relPath, $current);
  }

}
