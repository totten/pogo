<?php
namespace Pogo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetCommand extends BaseCommand {

  use DownloadCommandTrait;

  protected function configure() {
    $this
      ->setName('get')
      ->setDescription('Get dependencies for a PHP script')
      ->addArgument('script', InputArgument::REQUIRED, 'PHP script')
      ->addOption('dl', 'D', InputOption::VALUE_REQUIRED, 'Dependency download directory')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force download of any dependencies');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $script = $input->getArgument('script');
    if (empty($script)) {
      throw new \Exception("[get] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure

    if (!file_exists($script)) {
      throw new \Exception("[get] Non-existent file: {$script}");
    }

    $this->initProject($input, $script);

    return 0;
  }

}
