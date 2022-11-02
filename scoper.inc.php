<?php declare(strict_types = 1);

$polyfills = \array_merge(
  (array) glob(__DIR__ . '/vendor/symfony/polyfill-*/*.php'),
  (array) glob(__DIR__ . '/vendor/symfony/polyfill-*/Resources/stubs/*.php')
);

return [
  'prefix' => 'PogoPhar',
  'exclude-files' => array_merge(['templates/pogolib.php'], $polyfills),
  'exclude-namespaces' => ['Symfony\Polyfill'],
];
