<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class HelpCommand {

  public function run(PogoInput $input) {
    $cmd = basename($input->interpreter);
    $version = '@package_version@';
    $name = ($version{0} === '@') ? 'pogo (local version)' : 'pogo @package_version@';

    echo "$name\n";
    echo "Usage: $cmd [--<action>] [action-options] <script-file> [--] [script-options]\n";
    echo "\n";
    echo "Example: Run a script\n";
    echo "  $cmd my-script.php\n";
    echo "\n";
    echo "Example: Download dependencies for a script to a specific location\n";
    echo "  $cmd --get -d=/tmp/deps my-script.php\n";
    echo "\n";
    echo "Example: Update dependencies in an existing project directory\n";
    echo "  cd <out-dir>\n";
    echo "  $cmd --up\n";
    echo "\n";
    //    echo "Example: Remove any expired code from the common base folder\n";
    //    echo "  $cmd --clean\n";
    //    echo "\n";
    echo "Actions:\n";
    echo "  --get       Download dependencies, but do not execute.\n";
    echo "  --run       Run the script. Download anything necessary. (*default*)\n";
    echo "  --parse     Extract any pragmas or metadata from the script.\n";
    echo "  --up        Update dependencies (in current directory).\n";
    echo "  --help      Show help screen.\n";
    echo "\n";
    echo "Action-Options:\n";
    echo "  -f          Force; recreate project, even if it appears current\n";
    echo "  -d=<out>    Output dependencies in this directory\n";
    echo "\n";
    echo "Environment:\n";
    echo "  POGO_BASE   Default location for output folders\n";
    echo "              To store in-situ as a dot folder, use POGO_BASE=.\n";
    echo "              If omitted, defaults to ~/.cache/pogo or /tmp/pogo\n";
    echo "\n";
    return 0;
  }

}
