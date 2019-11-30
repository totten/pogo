<?php
namespace Pogo\Command;

use Pogo\PogoInput;
use Pogo\Runner\EvalRunner;
use Pogo\Runner\FileRunner;
use Pogo\Runner\DashBRunner;
use Pogo\Runner\DataRunner;
use Pogo\Runner\IncludeRunner;

class RunCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->script)) {
      throw new \Exception("[run] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure
    $target = $input->script;
    if (!file_exists($target)) {
      throw new \Exception("[run] Non-existent file: $target");
    }

    if (!empty($target) && file_exists($target)) {
      $project = $this->initProject($input, $target);

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
      $runMode = $input->getOption('run-mode', $project->scriptMetadata->runner['with']);
      $runMode = ($runMode === 'auto') ? $this->pickRunner($target) : $runMode;
      if (!isset($runners[$runMode])) {
        throw new \Exception("Invalid run mode: $runMode");
      }

      return $runners[$runMode]->run($autoloader, $project->scriptMetadata, $input->scriptArgs);
    }
    else {
      fwrite(STDERR, "[run] Script not found ($target)");
      return 1;
    }
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

}
