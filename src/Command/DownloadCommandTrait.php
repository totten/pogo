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
      $dl = $this->evalPathExpr($dl, $scriptMetadata);
      return PathUtil::evaluateDots(PathUtil::makeAbsolute($dl));
    }

    // Pick a base and calculate a hint/digested name.
    return $this->evalPathExpr('{POGO_BASE}/{SCRIPT_FILE}-{SCRIPT_DIGEST}', $scriptMetadata);
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

  /**
   * Evaluate the path-expression.
   *
   * @param string $pathExpr
   *   A path expression as used by `--dl` or `#!depdir`.
   *   Ex: '{SCRIPT_DIR}/.cache-{SCRIPT_FILE}'
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @return string
   */
  protected function evalPathExpr($pathExpr, \Pogo\ScriptMetadata $scriptMetadata) {
    return preg_replace_callback('/(_SCRIPTDIR_|{[A-Z0-9_]+}|{ENV\[[^\]]+\]})/',
      function ($m) use ($scriptMetadata) {
        $var = substr($m[1], 1, -1);

        if (preg_match('/^ENV\[([^\]]+)\]/', $var, $envParse)) {
          return getenv($envParse[1]);
        }

        switch ($var) {
          case 'CODE_DIGEST':
            return $this->getCodeDigest();

          case 'PHP_X':
            [$major, $minor, $patch] = explode('.', PHP_VERSION);
            return $major;

          case 'PHP_XY':
            [$major, $minor, $patch] = explode('.', PHP_VERSION);
            return "{$major}.{$minor}";

          case 'PHP_XYZ':
            [$major, $minor, $patch] = explode('.', PHP_VERSION);
            return "{$major}.{$minor}.{$patch}";

          case 'POGO_BASE':
            if (getenv('POGO_BASE')) {
              if (getenv('POGO_BASE') === '.') {
                return dirname($scriptMetadata->file) . DIRECTORY_SEPARATOR . '.pogo';
              }
              else {
                return getenv('POGO_BASE');
              }
            }
            elseif (getenv('HOME')) {
              return getenv('HOME') . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'pogo';
            }
            else {
              return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pogo';
            }

          case 'REQUIRE_DIGEST':
            return $scriptMetadata->getDigest();

          case 'SCRIPTDIR':
          case 'SCRIPT_DIR':
            return dirname($scriptMetadata->file);

          case 'SCRIPT_FILE':
            return basename($scriptMetadata->file);

          case 'SCRIPT_NAME':
            return preg_replace('/\.php$/', '', basename($scriptMetadata->file));

          case 'SCRIPT_DIGEST':
            return sha1($scriptMetadata->getDigest() . $this->getCodeDigest() . realpath($scriptMetadata->file));
        }
      },
      $pathExpr
    );
  }

}
