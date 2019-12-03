# TODO

* Add a command to cleanup stale data from POGO_BASE.
* Add more test-cases, esp:
    * Update dependencies after editing a script (`#!require:...`)
    * Do NOT update dependencies after editing a script (same requirement list)
    * Run a standalone download
    * Ini handling
    * With/without the --allow-stale option
* Add more verbosity options. Cleanup output.
* Make signature more like `php`
    * Accept code via stdin (if no files given)
    * Add `--ini`, `-d`, `-i`, `-v`, `-S`/`-t`, `-m`, `-n`
* Reconsider symfony/console. (Pro: All the runners have good thread-isolation. Con: We probably need ven more specialized arg parsing for simpler shebangs.)
* Consider integration with multifile composer projects. (Scan `**.php` and update `composer.json`)
* Add command to compile/export a static binary in PHAR format (e.g. `pogo --compile --phar=my-script.phar my-script.php`)
* When updating deps, be thread-safe/multi-process-safe
* Add some kind of pseudo requirement for the pogo version, e.g.`#!require {php: ~7.0, pogo: ~0.1}`
* If there are no `require` pragmas, then don't run `composer`. (Use a placeholder for `vendor/autoload.php`?)
* Figure out a way to use `composer` to resolve dependency graph and download URLs *without* using its autoloader/downloader. Instead, use a new autoloader and downloader to track packages in reusable folder (`~/.cache/pogo/symfony/yaml-3.3.3/`).
* Implement a composer plugin which supplements `composer.json:require` with info from scanned files (`{$psr0}/**.php`, `{$psr4}/**.php`)
* Windows...
