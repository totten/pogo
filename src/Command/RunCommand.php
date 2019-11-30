<?php
namespace Pogo\Command;

use Pogo\PogoProject;
use Pogo\PogoInput;
use Pogo\Runner\EvalRunner;
use Pogo\Runner\FileRunner;
use Pogo\Runner\DashBRunner;
use Pogo\Runner\DataRunner;
use Pogo\Runner\IncludeRunner;

class RunCommand {

  public function run(PogoInput $input) {
    $combo = array_merge($input->arguments, $input->suffix);
    if (count($combo) < 1) {
      throw new \Exception("[pogo run] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure
    $target = array_shift($combo);
    if (!file_exists($target)) {
      throw new \Exception("[pogo run] Non-existent file: $target");
    }

    if (!empty($target) && file_exists($target)) {
      $scriptMetadata = \Pogo\ScriptMetadata::parse($target);
      $path = $this->pickBaseDir($input, $target, function() use ($scriptMetadata) {
        return sha1($scriptMetadata->getDigest() . $this->getCodeDigest());
      });
      $project = new PogoProject($scriptMetadata, $path);

      $project->buildHelpers();
      if ($input->getOption(['force', 'f']) || in_array($project->getStatus(), ['empty', 'stale'])) {
        $project->buildComposer();
      }

      $autoloader = $project->path . '/vendor/autoload.php';
      if (!file_exists($autoloader)) {
        throw new \RuntimeException("Failed to generate autoloader: $autoloader");
      }

      $runners = [
        'dash-b' => new DashBRunner(),
        'data' => new DataRunner(),
        'eval' => new EvalRunner(),
        'file' => new FileRunner(),
        'include' => new IncludeRunner(),
      ];
      $runMode = $input->getOption('run-mode', $scriptMetadata->runMode);
      $runMode = ($runMode === 'auto') ? $this->pickRunner($target) : $runMode;
      if (!isset($runners[$runMode])) {
        throw new \Exception("Invalid run mode: $runMode");
      }

      return $runners[$runMode]->run($autoloader, $target, $combo);
    }
    else {
      fwrite(STDERR, "[pogo run] Script not found ($target)");
      return 1;
    }
  }

  /**
   * @param \Pogo\PogoInput $input
   * @param string $target
   *   The script file to execute.
   * @param callable $hintCb
   *   Function which generates a short-name for the script.
   * @return string
   */
  public function pickBaseDir(PogoInput $input, $target, $hintCb) {
    $result = $input->getOption(['out', 'o']);
    if ($result) {
      return $result;
    }

    // Pick a base and calculate a hint/digested name.

    if (getenv('POGO_BASE')) {
      if (getenv('POGO_BASE') === '.') {
        return dirname($target) . DIRECTORY_SEPARATOR . '.pogo';
      }
      $base = getenv('POGO_BASE');
    }
    elseif (getenv('HOME')) {
      $base = getenv('HOME') . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'pogo';
    }
    else {
      $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pogo';
    }
    return $result ? $result : $base . DIRECTORY_SEPARATOR . $hintCb();
  }

  public function pickRunner($file) {
    $code = file_get_contents($file);
    if (substr($code, 0, 3) === '#!/') {
      return 'eval';
    }
    else {
      return 'include';
    }
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
