<?php
namespace Pogo\Command;

use Pogo\Runner\EvalRunner;
use Pogo\Runner\FileRunner;
use Pogo\Runner\DashBRunner;
use Pogo\Runner\DataRunner;
use Pogo\Runner\IncludeRunner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends BaseCommand {

  use DownloadCommandTrait;

  protected function configure() {
    $this
      ->setName('run')
      ->setDescription('Execute a PHP script')
      ->addArgument('script', InputArgument::REQUIRED, 'PHP script')
      ->addArgument('script-args', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Arguments to pass through ')
      ->addOption('dl', 'D', InputOption::VALUE_REQUIRED, 'Dependency download directory')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force download of any dependencies')
      ->addOption('run-mode', NULL, InputOption::VALUE_REQUIRED, 'How to launch PHP subscripts (ex: include, eval)');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $target = $input->getArgument('script');
    if (empty($target)) {
      throw new \Exception("[run] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure
    if (!file_exists($target)) {
      throw new \Exception("[run] Non-existent file: $target");
    }

    if (!empty($target) && file_exists($target)) {
      $project = $this->initProject($input, $target);

      $autoloader = $project->getAutoloader();
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
      $runMode = $input->getOption('run-mode');
      $runMode = empty($runMode) ? $project->scriptMetadata->runner['with'] : $runMode;
      $runMode = ($runMode === 'auto') ? $this->pickRunner($target) : $runMode;
      if (!isset($runners[$runMode])) {
        throw new \Exception("Invalid run mode: $runMode");
      }

      $scriptArgs = $input->getArgument('script-args');
      return $runners[$runMode]->run($autoloader, $project->scriptMetadata, $scriptArgs);
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
