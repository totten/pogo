# pogo: Run PHP scripts with inline dependencies (v0.1)

Pogo allows you to write small PHP scripts which use PHP libraries (courtesy
of `composer`/`packagist`)...  but it *doesn't* require you setup a
special-purpose folder, project, or repository.  To use a dependency, simply
add a small pragma into your script.  This makes it easier to use PHP
libraries when:

* Experimenting or learning
* Writing random system-automation scripts and one-off scripts

## Example

Let's pick a small task that requires a few libraries -- suppose we want
to generate a pretty PDF from a source-code file (`.php`, `*.json`, etc).
We'll need a pretty-printer ([scrivo/highlight.php](https://github.com/scrivo/highlight.php))
and a PDF generator ([dompdf/dompdf](https://github.com/dompdf/dompdf)).

Skimming the README for each library, one finds a few introductory snippets.
I took these, added the `#!require` pragams, and improvised a little on the
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

Execute the script as one might normally do in the CLI, but change the `php` command to `pogo`.

```bash
pogo code2pdf.php < myfile.yml > myfile.pdf
```

That's it!

## Motivation

Most of my day-to-day work is in PHP, JS, and bash.  From time-to-time, one needs a bit of glue-code for one-offs, and
I find myself avoiding PHP for that task...  because using a library in PHP still requires bits of administrativa.
`pogo` is an experiment to reduce that administrativa.  Just create a `.php` file and run it.

## More information

* [Installation](docs/install.md): System requirements and install steps
* [Composer integration](docs/composer.md): How `pogo` works with `composer`
* [Execution](docs/exec.md): Ways to invoke scripts via `pogo`
* [FAQ](docs/faq.md): Frequently asked questions
* [Pragmas](docs/pragmas.md): List of all supported pragmas
* [Todo](docs/todo.md): Misc things that should be done
