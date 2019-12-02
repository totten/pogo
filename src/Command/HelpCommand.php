<?php
namespace Pogo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelpCommand extends \Symfony\Component\Console\Command\HelpCommand {

  protected function execute(InputInterface $input, OutputInterface $output) {
    if (!empty($input->getArgument('command')) && $input->getArgument('command') !== 'help') {
      return parent::execute($input, $output);
    }

    global $argv;
    $cmd = basename($argv[0]);
    $version = '@package_version@';
    $name = ($version{0} === '@') ? 'pogo (local version)' : 'pogo @package_version@';

    $output->writeln("$name");
    $output->writeln("<comment>Usage:</comment>");
    $output->writeln("  $cmd [<action>] [action-options] <script-file> [--] [script-options]");
    $output->writeln("");
    $output->writeln("<comment>Example: Run a script</comment>");
    $output->writeln("  $cmd my-script.php");
    $output->writeln("");
    $output->writeln("<comment>Example: Download dependencies for a script to a specific location</comment>");
    $output->writeln("  $cmd --get -D=/tmp/deps my-script.php");
    $output->writeln("");
    $output->writeln("<comment>Example: Update dependencies in an existing project directory</comment>");
    $output->writeln("  cd <out-dir>");
    $output->writeln("  $cmd --up");
    $output->writeln("");
    //    $output->writeln("Example: Remove any expired code from the common base folder");
    //    $output->writeln("  $cmd --clean");
    //    $output->writeln("");
    $output->writeln("<comment>Actions:</comment>");
    $output->writeln("  <info>--get</info>       Download dependencies, but do not execute.");
    $output->writeln("  <info>--run</info>       Run the script. Download anything necessary. (<comment>default</comment>)");
    $output->writeln("  <info>--parse</info>     Extract any pragmas or metadata from the script.");
    $output->writeln("  <info>--up</info>        Update dependencies (in current directory).");
    $output->writeln("  <info>--help</info>      Show help screen.");
    $output->writeln("");
    $output->writeln("<comment>Action-Options:</comment>");
    $output->writeln("  <info>-f</info>          Force; recreate project, even if it appears current");
    $output->writeln("  <info>-D=DIR</info>      Output dependencies in this directory");
    $output->writeln("");
    $output->writeln("<comment>Environment:</comment>");
    $output->writeln("  <info>POGO_BASE</info>   Default location for output folders");
    $output->writeln("              To store in-situ as a dot folder, use POGO_BASE=.");
    $output->writeln("              If omitted, defaults to ~/.cache/pogo or /tmp/pogo");
    $output->writeln("");
    return 0;
  }

}
