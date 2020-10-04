<?php
namespace Pogo\Command;

use Symfony\Component\Console\Input\InputInterface;

class DebugCommand extends RunCommand {

  protected function configure() {
    parent::configure();
    $this
      ->setName('dbg')
      ->setDescription('Execute a PHP script in debug mode');

    // Debugging: Prefer 'require' mode, because it avoids superfluous processes
    // and can handle breakpoints in the target script.
    $d = $this->getDefinition();
    $d->getOption('run-mode')->setDefault('require');
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @return string
   */
  public function pickBaseDir(InputInterface $input, $scriptMetadata) {
    if ($input->getOption('dl')) {
      return parent::pickBaseDir($input, $scriptMetadata);
    }

    // Default to downloading to an adjoining subdir because IDE's will
    // pick that up more easily.
    $dir = dirname(realpath($scriptMetadata->file));
    $hint = basename($scriptMetadata->file) . '-' . sha1(realpath($scriptMetadata->file));
    return $dir . DIRECTORY_SEPARATOR . '_pogodbg_' . $hint;
  }

}
