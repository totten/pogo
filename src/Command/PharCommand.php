<?php

namespace Pogo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PharCommand extends BaseCommand {

  use DownloadCommandTrait;

  protected function configure() {
    $this
      ->setName('phar')
      ->setDescription('Create a statically linked PHAR for the PHP script')
      ->addArgument('script', InputArgument::REQUIRED, 'PHP script')
      ->addOption('dl', 'D', InputOption::VALUE_REQUIRED, 'Dependency download directory')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force download of any dependencies')
      ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output file', '<BASE>.phar');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $script = $input->getArgument('script');
    if (empty($script)) {
      throw new \Exception("[get] Missing required file name");
    }

    if (!file_exists($script)) {
      throw new \Exception("[get] Non-existent file: {$script}");
    }

    $project = $this->initProject($input, $output, $script);
    $rand = bin2hex(random_bytes(6));

    $base = dirname($script) . '/' . preg_replace('/\.php$/', '', basename($script));
    $pharFinal = str_replace('<BASE>', $base, $input->getOption('out'));
    $pharTmp = preg_match(';\.phar;', $pharFinal) ? $pharFinal : (sys_get_temp_dir() . "/" . basename("$pharFinal-$rand.phar"));

    $output->writeln(sprintf("<info>Generating <comment>%s</comment> from <comment>%s</comment></info>", $pharFinal, $script));

    $fs = new Filesystem();

    $logicalPhar = sprintf('pogo-export-%s.phar', $rand);
    $mainFile = sprintf('main-%s.php', $rand);
    $mainFullPath = $project->path . DIRECTORY_SEPARATOR . $mainFile;
    try {
      $output->writeln(sprintf(" - Main file: <comment>%s</comment>", $mainFile), OutputInterface::VERBOSITY_VERBOSE);
      $fs->dumpFile($mainFullPath, $this->createMain($script));

      $output->writeln(sprintf(" - PHAR file: <comment>%s</comment>", $pharTmp), OutputInterface::VERBOSITY_VERBOSE);
      $fs->remove($pharTmp);
      $phar = new \Phar($pharTmp);

      $phar->setStub($this->createStub($logicalPhar, $mainFile));
      $phar->buildFromDirectory($project->path);
      $phar->compressFiles(\Phar::GZ);
      $fs->chmod($pharTmp, 0777);

      if ($pharTmp !== $pharFinal) {
        $output->writeln(sprintf(" - Rename: <comment>%s</comment> => <comment>%s</comment>", $pharTmp, $pharFinal), OutputInterface::VERBOSITY_VERBOSE);
        $fs->remove($pharFinal);
        $fs->rename($pharTmp, $pharFinal);
      }

      $output->writeln(sprintf(" - Finished: <comment>%s</comment>", $pharFinal), OutputInterface::VERBOSITY_VERBOSE);

    } finally {
      $fs->remove($mainFullPath);
    }

    return 0;
  }

  /**
   * @param string $scriptFile
   *   PHP file to read as input
   * @return string
   *   PHP code/file content, with any filters any applied.
   */
  protected function createMain($scriptFile) {
    $lines = explode("\n", file_get_contents($scriptFile));

    if (preg_match(';^#!;', $lines[0])) {
      array_shift($lines);
    }

    return implode("\n", $lines);
  }

  /**
   * @param string $logicalPharName
   *   A logical name for this file. Used at runtime for internal path-mappings.
   * @param string $mainFile
   *   Relative path to the main script (within the archive).
   * @return string
   *   PHP code
   */
  protected function createStub($logicalPharName, $mainFile) {
    $export = function ($x) {
      return var_export($x, 1);
    };
    $stub = implode("\n", [
      "#!/usr/bin/env php",
      "<" . "?php",
      sprintf("Phar::mapPhar(%s);", $export($logicalPharName)),
      sprintf("require %s;", $export("phar://$logicalPharName/vendor/autoload.php")),
      sprintf("require %s;", $export("phar://$logicalPharName/$mainFile")),
      "__HALT_COMPILER();",
    ]);
    return $stub;
  }

}
