<?php
/**
 * @return string
 *   The content of QP_SCRIPT, without any leading shebangs.
 */
function pogo_script() {
  return pogo_clean_script(file_get_contents(getenv('QP_SCRIPT')));
}

/**
 * @param string $c
 *   PHP code, which may begin with a shebang
 * @return string
 *   PHP code, without a shebang
 */
function pogo_clean_script($c) {
  $lines = explode("\n", $c, 3);
  $hasShebang = substr($lines[0], 0, 3) === '#!/';
  if (!$hasShebang) {
    // Huzza! It's perfect just as it is.
    return $c;
  }

  $tail = count($lines) === 3 ? $lines[2] : '';
  if (preg_match(';^<' . '\?;', $lines[1])) {
    // Common case: Typical PHP shebang with opening code block.
    // We strip first line, and insert an extra newline so that
    // line-numbers still match up. The newline goes after the opening of
    // of the code-block so that it doesn't get displayed.
    return $lines[1] . "\n\n" . $tail;
  }
  else {
    // Uncommon case: Shebang which is not followed by opening code block.
    // We strip it to be function correctly, but line-numbers won't match up.
    return $lines[1] . "\n" . $tail;
  }

}