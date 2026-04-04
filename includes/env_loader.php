<?php

namespace App;

class EnvLoader
{
  public static function load($path)
  {
    if (!file_exists($path)) {
      return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      // Skip comments
      if (strpos(trim($line), '#') === 0) {
        continue;
      }

      // Split khi gặp "="
      list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      $value = trim($value);

      // Cài env vars
      if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
      }
    }
  }
}