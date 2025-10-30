<?php

$dirs = [
  BASE_PATH . '/includes/core',
  BASE_PATH . '/views/components',
  BASE_PATH . '/views/layouts',
  BASE_PATH . '/views',
];

foreach ($dirs as $dir) {
  if (is_dir($dir)) {
    foreach (glob($dir . '/*.php') as $file) {
      require_once $file;
    }
  }
}
