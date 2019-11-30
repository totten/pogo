# pogo: Run PHP with inline dependencies (v0.1)

Pogo allows you to write small PHP scripts which use PHP libraries (courtesy
of `composer`/`packagist`)...  but it *doesn't* require you setup a
special-purpose folder, project, or repository. To use a dependency, you can
simply drop a small pragma into your script, e.g.

```php
#!require symfony/yaml: ~3.0
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
#!require symfony/yaml: ~3.0
#!require dompdf/dompdf: ~0.8.3
$yaml = Symfony\Component\Yaml\Yaml::parse(file_get_contents('php://stdin'));
$html = '<pre>' . htmlentities(print_r($yaml, 1)) . '</pre>';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream();
```

Execute the script as one might normally do in the CLI, but change the `php` command to `pogo run`.

```bash
pogo run yaml-pdf.php < myfile.yml > myfile.pdf
```

## More information

* [Pogo and Composer](docs/composer.md): How the tools interact
* [Pogo and Executables](docs/exec.md): How to use pogo with other executables
* [Pragmas](doc/pragmas.md): List of all supported pragmas
* [Install](docs/install.md): System requirements along with download/install steps
* [Todo](docs/todo.md): Misc things that should be done
