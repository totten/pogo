# pogo: Run PHP with inline dependencies (v0.1)

Pogo allows you to write small PHP scripts which use PHP libraries (courtesy
of `composer`/`packagist`)...  but it *doesn't* require you setup a
special-purpose folder, project, or repository.  To use a dependency, simply
add a small pragma into your script.  This makes it easier to use PHP
libraries when:

* Experimenting or learning
* Writing random system-automation scripts and one-off scripts

## Example

Let's pick some small task that requires a few libraries -- suppose we want
to generate a pretty PDF from a source-code file (`.php`, `*.json`, etc).
We'll need a pretty-printer ([scrivo/highlight.php](https://github.com/scrivo/highlight.php))
and a PDF generator ([dompdf/dompdf](https://github.com/dompdf/dompdf)).

Create the standalone script `code2pdf.php`:

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

Execute the script as one might normally do in the CLI, but change the `php` command to `pogo run`.

```bash
pogo run yaml-pdf.php < myfile.yml > myfile.pdf
```

That's it!

## More information

* [Installation](docs/install.md): System requirements along with download/install steps
* [Composer integration](docs/composer.md): How `pogo` works with `composer`
* [Execution](docs/exec.md): Ways to invoke scripts via `pogo`
* [Pragmas](docs/pragmas.md): List of all supported pragmas
* [Todo](docs/todo.md): Misc things that should be done
