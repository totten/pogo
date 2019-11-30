# pogo: Run PHP with inline dependencies (v0.1)

Pogo allows you to write small PHP scripts which use PHP libraries (courtesy
of `composer`/`packagist`)...  but it *doesn't* require you setup a
special-purpose folder, project, or repository. To use a dependency, you can
simply drop a small pragma into your script, e.g.

```php
#!require: symfony/yaml ~3.0
$parsedYaml = Symfony\Component\Yaml\Yaml::parse($rawYaml);
```

This makes it easier to use PHP libraries when:

* Experimenting or learning
* Writing random system-automation scripts and one-off scripts

## Example

Let's pick some small task that requires a few libraries -- suppose we want
to read a YAML file and pretty-print the content as a PDF file.  Call this
script `yaml2pdf.php`:

```php
<?php
#!require: symfony/yaml ~3.0
#!require: dompdf/dompdf ~0.8.3
$yaml = Symfony\Component\Yaml\Yaml::parse(file_get_contents('php://stdin'));
$html = '<pre>' . htmlentities(print_r($yaml, 1)) . '</pre>';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream();
```

Now execute this command:

```bash
pogo run yaml-pdf.php < myfile.yml > myfile.pdf
```

# Pogo and Composer

Internally, `pogo` uses `composer` to download the dependencies and store them in a hidden folder.
This may be useful if you are debugging or integrating with another build/package-system.

By default, `pogo` will put dependencies in `$HOME/.cache/pogo/<digest>`, where `<digest>` is a computed value
that depends on your list of requirements. You may tune the defaults with the `POGO_BASE` variable, e.g.

* `POGO_BASE=/var/cache/pogo`: Store builds in a shared folder
* `POGO_BASE=.`: Store builds in a dot-folder, adjacent to the executed script.

For a specific script, you may optionally exercise fine-grained control over the dependency downloads. These may be handy if you've got
an existing script and wish to work with it in an IDE or debugger.

```bash
## Download dependencies to a specific folder - and run the script.
pogo run <script-file> -o=<dep-dir>

## Download dependencies to a specific folder.
pogo dl <script-file> -o=<dep-dir>

## Update dependencies in a previously downloaded folder
## Equivalent to re-running "pogo dl <same-script> -o=<same-output>"
cd <dep-dir>
pogo up
```

# Pogo and Executables

When creating a PHP script, you can call `pogo run`:

```bash
pogo run my-script.php
```

Alternatively, you create an executable script:

```php
echo '#!/usr/bin/env pogo --' > my-script
echo '<?php' >> my-script
echo 'echo "Hello world\n";' >> my-script
chmod +x my-script
```

# Requirements

* PHP
* `composer`
* (Only tested in Linux/OS X)

# Download / Install

For the moment:

```bash
git clone https://github.com/totten/pogo
cd pogo
composer install
export PATH=$PWD/bin:$PATH
```

Optionally, instead of updating the `PATH`, you can use
[`box`](http://box-project.github.io/box2/) to create a PHAR and copy it to
your `bin` folder:

```bash
$ git clone https://github.com/totten/pogo && cd pogo && composer install
$ which box
/usr/local/bin/box
$ php -dphar.readonly=0 /usr/local/bin/box build
$ sudo cp bin/pogo.phar /usr/local/bin/pogo
```

# Pragmas

Pogo accepts instructions using a `#!foo: bar` notation. The following are supported:

* `#!require: <package> <version>`
    * The `<package>` and `<version>` notations match [composer's `require`](https://getcomposer.org/doc/04-schema.md#require).
* `#!ttl: <qty> <unit>`
    * The time-to-live determines the maximum time to retain previously downloaded dependencies.
    * The `<unit>` can be `sec`, `min`, `hour`, `day`, `week`, `month`, `year`.
    * The `<qty> <unit>` notation is a strict subset of [PHP `strtotime()`](php.net/strtotime).
* `#!run: <mode>`
    * After `pogo` updates the dependencies, it needs to call `php` and execute your script. Unfortunately, I have not found
      a perfect technique for this delegation. The defaults should generally execute correctly. However, if a script begins
      with `#!/usr/bin/env ...`, and if you're doing some debugging, then you may want to try a different mode. Options:
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

# TODO

* Publish PHAR
* Add test-cases, esp:
    * Run a basic script (no shebang)
    * Run an executable script (with shebang)
    * Repeat above with every `#!run:` mode
    * Update dependencies after editing a script (`#!require:...`)
    * Do NOT update dependencies after editing a script (same requirement list)
    * Run a standalone download
    * Attempt to run a script with an invalid set of requirements
* Add more verbosity options. Cleanup output.
* Reconsider symfony/console. (Pro: All the runners have good thread-isolation. Con: We probably need ven more specialized arg parsing for simpler shebangs.)