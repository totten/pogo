<?php
namespace Pogo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends BaseCommand {

  protected function configure() {
    $this
      ->setName('parse')
      ->setDescription('Extract any pragmas or metadata from the script')
      ->addArgument('script', InputArgument::REQUIRED, 'PHP script');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $script = $input->getArgument('script');

    if (empty($script)) {
      throw new \Exception("[parse] Missing required file name");
    }

    if (!file_exists($script)) {
      throw new \Exception("[parse] Non-existent file: {$script}");
    }

    $scriptMetadata = \Pogo\ScriptMetadata::parse($script);
    $scriptMetadata = (array) $scriptMetadata;

    $output->writeln(json_encode($scriptMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      OutputInterface::OUTPUT_RAW);

    return 0;
  }

}
