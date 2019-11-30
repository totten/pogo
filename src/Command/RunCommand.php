<?php
namespace Qp\Command;

use Qp\QpProject;
use Qp\QpInput;
use Qp\Runner\EvalRunner;
use Qp\Runner\FileRunner;
use Qp\Runner\DashBRunner;
use Qp\Runner\DataRunner;
use Qp\Runner\IncludeRunner;

class RunCommand {

  public function run(QpInput $input) {
    $combo = array_merge($input->arguments, $input->suffix);
    if (count($combo) < 1) {
      throw new \Exception("[qp run] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure
    $target = array_shift($combo);
    if (!file_exists($target)) {
      throw new \Exception("[qp run] Non-existent file: $target");
    }

    if (!empty($target) && file_exists($target)) {
      $scriptMetadata = \Qp\ScriptMetadata::parse($target);
      $path = $this->pickBaseDir($input, function() use ($scriptMetadata) {
        return sha1($scriptMetadata->getDigest() . $this->getCodeDigest());
      });
      $project = new QpProject($scriptMetadata, $path);

      $project->buildHelpers();
      if ($input->getOption(['force','f']) || in_array($project->getStatus(), ['empty', 'stale'])) {
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
      fwrite(STDERR, "[qp run] Script not found ($target)");
      return 1;
    }
  }

  /**
   * @param \Qp\QpInput $input
   * @param callable $hintCb
   *   Function which generates
   * @return string
   */
  public function pickBaseDir(QpInput $input, $hintCb) {
    $base = getenv('QP_BASE')
      ? getenv('QP_BASE')
      : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qp';
    $result = $input->getOption(['out', 'o']);
    // Only run hintCb() if needed.
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
        "$base/templates/qplib.php",
      ];
      $digests = array_map(function($f){
        return sha1_file($f);
      }, $files);
      $value = sha1(implode('', $digests));
    }
    return $value;
  }

}
