<?php
namespace Pogo\Command;

use Pogo\PogoInput;
use Pogo\PogoProject;

trait DownloadCommandTrait {

  /**
   * @param \Pogo\PogoInput $input
   * @param string $target
   * @return \Pogo\PogoProject
   */
  public function initProject(PogoInput $input, $target) {
    $scriptMetadata = \Pogo\ScriptMetadata::parse($target);
    $path = $this->pickBaseDir($input, $scriptMetadata);
    $project = new PogoProject($scriptMetadata, $path);

    $project->buildHelpers();
    if ($input->getOption(['force', 'f'])
      || in_array($project->getStatus(), ['empty', 'stale'])
    ) {
      $project->buildComposer();
      return $project;
    }
    return $project;
  }

  /**
   * @param \Pogo\PogoInput $input
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @return string
   */
  public function pickBaseDir(PogoInput $input, $scriptMetadata) {
    $result = $input->getOption(['out', 'o']);
    if ($result) {
      return $result;
    }

    // Pick a base and calculate a hint/digested name.
    $hint = basename($scriptMetadata->file) . '-' . sha1($scriptMetadata->getDigest() . $this->getCodeDigest() . realpath($scriptMetadata->file));

    if (getenv('POGO_BASE')) {
      if (getenv('POGO_BASE') === '.') {
        $base = dirname($scriptMetadata->file) . DIRECTORY_SEPARATOR . '.pogo';
      }
      else {
        $base = getenv('POGO_BASE');
      }
    }
    elseif (getenv('HOME')) {
      $base = getenv('HOME') . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'pogo';
    }
    else {
      $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pogo';
    }
    return $base . DIRECTORY_SEPARATOR . $hint;
  }

  /**
   *
   */
  public function getCodeDigest() {
    static $value = NULL;
    if ($value === NULL) {
      $base = dirname(dirname(__DIR__));
      $files = [
        "$base/templates/pogolib.php",
      ];
      $digests = array_map(function($f) {
        return sha1_file($f);
      }, $files);
      $value = sha1(implode('', $digests));
    }
    return $value;
  }

}
