<?php
namespace Pogo;

use LesserEvil\ShellVerbosityIsEvil;
use Pogo\Command\DebugCommand;
use Pogo\Command\GetCommand;
use Pogo\Command\HelpCommand;
use Pogo\Command\ParseCommand;
use Pogo\Command\PharCommand;
use Pogo\Command\RunCommand;
use Pogo\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application {

  /**
   * Primary entry point for execution of the standalone command.
   *
   * @param array $args
   */
  public static function main($args) {
    $version = '@package_version@';

    $input = new ArgvInput(PogoInput::filter($args));

    $application = new Application('pogo', ($version[0] === '@') ? '(local version)' : $version);
    $application->setAutoExit(FALSE);
    $application->setCatchExceptions(TRUE);
    $application->setDefaultCommand('the-input-filter-should-prevent-using-this');
    return $application->run($input);
  }

  /**
   * Gets the default commands that should always be available.
   *
   * @return \Symfony\Component\Console\Command\Command[] An array of default Command instances
   */
  protected function getDefaultCommands() {
    return [
      new HelpCommand(),
      new ParseCommand(),
      new PharCommand(),
      new GetCommand(),
      new RunCommand(),
      new DebugCommand(),
      new UpdateCommand(),
    ];
  }

  /**
   * Gets the default commands that should always be available.
   *
   * @return \Symfony\Component\Console\Command\Command[] An array of default Command instances
   */
  public function getAllCommands() {
    return $this->getDefaultCommands();
  }

  protected function configureIO(InputInterface $input, OutputInterface $output) {
    ShellVerbosityIsEvil::doWithoutEvil(function() use ($input, $output) {
      parent::configureIO($input, $output);
    });
  }

}
