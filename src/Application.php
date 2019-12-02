<?php
namespace Pogo;

use Pogo\Command\GetCommand;
use Pogo\Command\HelpCommand;
use Pogo\Command\ParseCommand;
use Pogo\Command\RunCommand;
use Pogo\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArgvInput;

class Application extends \Symfony\Component\Console\Application {

  /**
   * Primary entry point for execution of the standalone command.
   *
   * @param array $args
   */
  public static function main($args) {
    $version = '@package_version@';

    // FIXME: this handles "-D=foo script.php" but not "-D foo script.php"
    $pogoInput = PogoInput::create($args);
    $input = new ArgvInput($pogoInput->encode());

    $application = new Application('pogo', ($version{0} === '@') ? '(local version)' : $version);
    $application->setAutoExit(FALSE);
    $application->setCatchExceptions(TRUE);
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
      new GetCommand(),
      new RunCommand(),
      new UpdateCommand(),
    ];
  }

}
