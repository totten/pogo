# PHP On the Go (`pogo`)

Pogo allows you to write small PHP scripts which use PHP libraries (courtesy
of `composer`/`packagist`)...  but it *doesn't* require you setup a
special-purpose folder, project, or repository.  To use a dependency, simply
add a small pragma into your script. For example:

```php
#!require symfony/yaml: ~4.4
```

This makes it easier to use PHP libraries for glue-scripts, throw-away scripts, quick experiments, etc.

## Example

Let's pick a small task that requires a few libraries -- suppose we want
to generate a pretty PDF from a source-code file (`*.php`, `*.json`, etc).
We'll need a pretty-printer ([scrivo/highlight.php](https://github.com/scrivo/highlight.php))
and a PDF generator ([dompdf/dompdf](https://github.com/dompdf/dompdf)).

Skimming the README for each library, one finds a few introductory snippets.
I took these, added the `#!require` pragmas, and improvised a little on the
`$html` variable. This becomes a small script, `code2pdf.php`:

```php
<?php
$code = file_get_contents('php://stdin');

#!require scrivo/highlight.php: ~9.15
$hl = new \Highlight\Highlighter();
$hl->setAutodetectLanguages(['php', 'css', 'yaml', 'json', 'js']);
$highlighted = $hl->highlightAuto($code);
$html = sprintf('<link rel="stylesheet" href="file://%s"/>', \HighlightUtilities\getStyleSheetPath('sunburst.css'));
$html .= sprintf("<pre><code class=\"hljs %s\">%s</code></pre>", $highlighted->language, $highlighted->value);

#!require dompdf/dompdf: ~0.8.3
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream();
```

To run this script in the CLI, just use:

```bash
pogo code2pdf.php
```

Of course, this script expects some content as input (e.g. `myfile.yml`) and produces a PDF as output (e.g. `myfile.pdf`), so a more realistic command would be

```bash
cat myfile.yml | pogo code2pdf.php > myfile.pdf
```

That's it!

## More examples

The [examples](./examples) folder has several examples of using other libraries and frameworks, such as [ReactPHP](examples/httpd-react.php), [Symfony Console](examples/cli-symfony.php), [Clippy](examples/cli-clippy.php), and [Robo](examples/cli-robo.php). Each example is an executable program.

## Motivation

Most of my day-to-day work is in PHP, JS, and bash.  From time-to-time, one needs a bit of glue-code for one-offs, and
I find myself avoiding PHP for that task...  because using a library in PHP still requires bits of administrativa.
`pogo` is an experiment to reduce that administrativa.  Just create a `.php` file and run it.

## Documentation

* [Installation](docs/install.md): System requirements and install steps
* [Composer integration](docs/composer.md): How `pogo` works with `composer`
* [Execution](docs/exec.md): Ways to invoke scripts via `pogo`
* [Compile to PHAR](docs/phar.md): How to create a `phar` using `pogo`
* [FAQ](docs/faq.md): Frequently asked questions
* [Pragmas](docs/pragmas.md): List of all supported pragmas
* [Todo](docs/todo.md): Misc things that should be done

## Related

* [Clippy](https://github.com/clippy-php/std): A variant of `symfony/console` optimized for scripting.
