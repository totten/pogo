<?php
namespace Pogo\Command;

use Pogo\Runner\PrependRunner;
use Pogo\Runner\EvalRunner;
use Pogo\Runner\DataRunner;
use Pogo\Runner\RequireRunner;
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
      ->addOption('allow-stale', NULL, InputOption::VALUE_NONE, 'Do not attempt automatic updates. Reuse previous download, even if stale')
      ->addOption('run-mode', NULL, InputOption::VALUE_REQUIRED, 'How to launch PHP subscripts (ex: include, eval)');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $target = $input->getArgument('script');
    if (empty($target)) {
      throw new \Exception("[run] Missing required file name");
    }

    $output->writeln("<info>Running <comment>$target</comment></info>", OutputInterface::VERBOSITY_VERBOSE);

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure
    if (!file_exists($target)) {
      throw new \Exception("[run] Non-existent file: $target");
    }

    if (!empty($target) && file_exists($target)) {
      $project = $this->initProject($input, $output, $target);

      $autoloader = $project->getAutoloader();
      if (!file_exists($autoloader)) {
        throw new \RuntimeException("Failed to generate autoloader: $autoloader");
      }

      if ($input->getOption('run-mode')) {
        $project->scriptMetadata->parseCode("<?php\n#!run " . $input->getOption('run-mode'));
      }
      list($runnerName, $runner) = $this->pickRunner($project->scriptMetadata);

      $scriptArgs = $input->getArgument('script-args');
      $output->writeln(sprintf("<info>Calling runner <comment>%s</comment> with args \"<comment>%s</comment>\"</info>",
        $runnerName, implode(' ', $scriptArgs)), OutputInterface::VERBOSITY_VERBOSE);

      return $runner->run($autoloader, $project->scriptMetadata, $scriptArgs);
    }
    else {
      fwrite(STDERR, "[run] Script not found ($target)");
      return 1;
    }
  }

  /**
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @return array
   * @throws \Exception
   */
  protected function pickRunner($scriptMetadata) {
    $runners = [
      'data' => new DataRunner(),
      'eval' => new EvalRunner(),
      'require' => new RequireRunner(),
      'prepend' => new PrependRunner(),
    ];

    switch ($scriptMetadata->runner['with']) {
      case 'auto':
      case 'isolate':
        $runMode = 'prepend';
        break;

      case 'local':
        $code = file_get_contents($scriptMetadata->file);
        $runMode = (substr($code, 0, 3) === '#!/') ? 'eval' : 'require';
        break;

      default:
        $runMode = $scriptMetadata->runner['with'];
        break;
    }

    $runMode = ($runMode === 'auto') ? 'prepend' : $runMode;
    if (!isset($runners[$runMode])) {
      throw new \Exception("Invalid run mode: $runMode");
    }
    return array($runMode, $runners[$runMode]);
  }

}
