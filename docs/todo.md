# TODO

* Publish PHAR
* Add test-cases, esp:
    * Run a basic script (no shebang)
    * Run an executable script (with shebang)
    * Repeat above with every `#!run:` mode
    * Update dependencies after editing a script (`#!require:...`)
    * Do NOT update dependencies after editing a script (same requirement list)
    * Run a standalone download
    * Run the parse command
    * Attempt to run a script with an invalid set of requirements
    * Ini handling
* Add more verbosity options. Cleanup output.
* Remove includes
* Reconsider symfony/console. (Pro: All the runners have good thread-isolation. Con: We probably need ven more specialized arg parsing for simpler shebangs.)
* Consider integration with multifile composer projects. (Scan `**.php` and update `composer.json`)
* Windows...
