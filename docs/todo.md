# TODO

* Add a command to cleanup stale data from POGO_BASE.
* Add more test-cases, esp:
    * Update dependencies after editing a script (`#!require:...`)
    * Do NOT update dependencies after editing a script (same requirement list)
    * Run a standalone download
    * Ini handling
* Add more verbosity options. Cleanup output.
* Make signature more like `php`
    * Accept code via stdin (if no files given)
    * Add `--ini`, `-d`, `-i`, `-v`, `-S`/`-t`, `-m`, `-n`
* Reconsider symfony/console. (Pro: All the runners have good thread-isolation. Con: We probably need ven more specialized arg parsing for simpler shebangs.)
* Consider integration with multifile composer projects. (Scan `**.php` and update `composer.json`)
* Add command to compile/export a static binary in PHAR format (e.g. `pogo --compile --phar=my-script.phar my-script.php`)
* When updating deps, be thread-safe/multi-process-safe
* If there are no `require` pragmas, then don't run `composer`. (Use a placeholder for `vendor/autoload.php`?)
* Windows...
