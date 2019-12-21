<?php
namespace Pogo\Command;

use Pogo\PathUtil;
use Pogo\PogoProject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait DownloadCommandTrait {

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @param string $target
   * @return \Pogo\PogoProject
   */
  public function initProject(InputInterface $input, OutputInterface $output, $target) {
    $scriptMetadata = \Pogo\ScriptMetadata::parse($target);
    $path = $this->pickBaseDir($input, $scriptMetadata);
    $output->writeln("<info>Dependencies stored in <comment>$path</comment></info>", OutputInterface::VERBOSITY_VERBOSE);
    $project = new PogoProject($scriptMetadata, $path);

    $status = $project->getStatus();
    $allowStale = $input->hasOption('allow-stale') && $input->getOption('allow-stale');

    if ($input->getOption('force')) {
      $output->writeln("<info>Building helpers</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildHelpers();
      $output->writeln("<info>Building composer</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildComposer();
    }
    elseif ($status === 'empty' || $status === 'broken') {
      $output->writeln("<info>Building helpers</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildHelpers();
      $output->writeln("<info>Building composer</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildComposer();
    }
    elseif ($status === 'stale' && !$allowStale) {
      $output->writeln("<info>Building helpers</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildHelpers();
      $output->writeln("<info>Building composer</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildComposer();
    }
    elseif ($status === 'stale' && $allowStale) {
      // null op
    }
    elseif ($status === 'current') {
      // This is handy in dev (when tweaking helper code), but maybe it shouldn't be here...
      $output->writeln("<info>Building helpers</info>", OutputInterface::VERBOSITY_VERBOSE);
      $project->buildHelpers();

    }
    $output->writeln("<info>Project initialized</info>", OutputInterface::VERBOSITY_VERBOSE);
    return $project;
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @return string
   */
  public function pickBaseDir(InputInterface $input, $scriptMetadata) {
    $dl = $input->getOption('dl');
    if (!$dl && $scriptMetadata->dir) {
      $dl = $scriptMetadata->dir;
    }
    if ($dl) {
      $dl = preg_replace('/^_SCRIPTDIR_/', dirname($scriptMetadata->file), $dl);
      return PathUtil::evaluateDots(PathUtil::makeAbsolute($dl));
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
