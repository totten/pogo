# Pragmas

Pogo accepts instructions using a `#!foo` notation. The following are supported:

## require

* __Signature__: `#!require {...yaml...}`
* __Description__: Download a package via composer/packagist
* __Comment__: The `<package>` and `<version>` notations match [composer's `require`](https://getcomposer.org/doc/04-schema.md#require).
* __Example__: `#!require symfony/yaml: ~3.0`
* __Example__: `#!require {symfony/yaml: ~3.0, symfony/finder: ~3.0}`

## ini

* __Signature__: `#!ini {...yaml...}`
* __Description__: Set the value of `php.ini` option *before* launching the script.
* __Example__: `#!ini variables_order: EGPS`
* __Example__: `#!ini {max_upload_size: 1m, memory_limit: 1g}`

## ttl

* __Signature__: `#!ttl <qty> <unit>`
* The time-to-live determines the maximum time to retain previously downloaded dependencies.
* The `<unit>` can be `sec`, `min`, `hour`, `day`, `week`, `month`, `year`.
* The `<qty> <unit>` notation is a strict subset of [PHP `strtotime()`](php.net/strtotime).
* __Example__: `#!ttl 7 days`

## run

* __Signature__: `#!run <mode>`
* __Description__: After `pogo` updates the dependencies, it needs to call `php` and execute your script. Unfortunately, I have not found
  a perfect technique for this delegation. The defaults should generally execute correctly. However, if a script begins
  with `#!/usr/bin/env ...`, and if you're doing some debugging, then you may want to try a different mode.
* __Options__:
    * `auto`: Let `pogo` pick a mechanism. Generally, it will use `include` or `eval` (depending on whether the
      script begins with `#!/usr/bin/env ...`.
    * `include`: Loosely, this runs `php -r 'require_once $autoloader; include $your_script;'. It should behave the
      most intuitively with respect to debugging, avoiding unnecessary file IO/duplication, CLI inputs/outputs, etc.
      However, if `$your_script` is a standalone program (`#!/usr/bin/env pogo run --`), then it will erroneously output
      the first line.
    * `eval`: Loosely, this runs `php -r 'require_once $autoloader; eval(cleanup($your_script))'`. This fixes the
      erroneous output, but backtraces and debugging may not be as pleasant.
    * `dash-b`: Loosely, this runs `echo | php -B 'require_once $autoloader;' -F $your_script`. This avoids the
      erroneous output and gives decent backtraces, but it will not handle piped-input correctly.
    * `data`: This is every similar to `eval`. At the moment, I dont' think it has any real advantage over `eval`,
      but I've kept it as a potential inspiration.
