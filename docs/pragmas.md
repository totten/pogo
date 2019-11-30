# Pragmas

Pogo accepts instructions using a `#!foo` notation. The following are supported:

## require

* __Description__: Download a package via composer/packagist
* __Signature__: `#!require <yaml>`
* __Example__: `#!require symfony/yaml: ~3.0`
* __Example__: `#!require {symfony/yaml: ~3.0, symfony/finder: ~3.0}`
* __Comment__: The `<package>` and `<version>` notations match [composer's `require`](https://getcomposer.org/doc/04-schema.md#require).

## ini

* __Description__: Set the value of `php.ini` option *before* launching the script.
* __Signature__: `#!ini <yaml>`
* __Example__: `#!ini variables_order: EGPS`
* __Example__: `#!ini {max_upload_size: 1m, memory_limit: 1g}`

## ttl

* __Description__: The time-to-live determines the maximum time to retain previously downloaded dependencies.
    * The `<qty> <unit>` notation is a strict subset of [PHP `strtotime()`](php.net/strtotime).
    * The `<unit>` can be `sec`, `min`, `hour`, `day`, `week`, `month`, `year`.
* __Signature__: `#!ttl <qty> <unit>`
* __Example__: `#!ttl 7 days`
* __Comment__: Any change in dependencies will trigger a new download -- regardless of `ttl`. So what is `ttl` for?
  It allows scripts to remain reasonably secure (up-to-date) by default.  If you don't want this, you can always peg versions more precisely (`~3.0` vs `3.4.20`) or a set a long a `ttl` (`10 years`).

## run

* __Description__: After `pogo` updates the dependencies, it needs to call `php` and execute your script. Unfortunately, I have not found
  a great, one-size-fits-all technique for this delegation. The defaults should *execute correctly* in most scripting scenarios - but
  support for *nice backtraces* might be compromised if you use a `#!/usr/bin/env pogo...` header. In that case, you might want to
  try a different mode.
* __Signature__: `#!run <mode>`
* __Example__: `#!run dash-b`
* __Options__:
    * `auto`: Let `pogo` pick a mechanism. Generally, it will use `include` or `eval` (depending on whether the
      script begins with `#!/usr/bin/env pogo` (or similar).
    * `include`: Loosely, this runs `php -r 'require_once $autoloader; include $your_script;'. Among these runners,
      it should behave the most intuitively with respect to debugging, avoiding unnecessary file IO/duplication, CLI
      inputs/outputs, etc. However, if `$your_script` is a standalone program (`#!/usr/bin/env pogo run --`), then
      it will erroneously output the first line.
    * `eval`: Loosely, this runs `php -r 'require_once $autoloader; eval(cleanup($your_script))'`. This fixes the
      erroneous output, but backtraces and debugging may not be as pleasant.
    * `dash-b`: Loosely, this runs `echo | php -B 'require_once $autoloader;' -F $your_script`. This avoids the
      erroneous output and gives decent backtraces, but it will not handle piped-input correctly.
    * `data`: This is every similar to `eval`. At the moment, I dont' think it has any real advantage over `eval`,
      but I've kept it as a potential inspiration.
