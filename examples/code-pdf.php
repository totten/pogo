#!/usr/bin/env pogo
<?php

## This example reads source-code from STDIN then formats it and prints it as a PDF on STDOUT.
##
## Usage: pogo code-pdf.php < mysource.php > mysource.pdf

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
