<?php
namespace App\Core;

class Response
{
  protected $status;
  protected $headers = [];
  protected $content;

  public function __construct($content = '', $status = 200, $headers = [])
  {
    $this->status = $status;
    $this->headers = $headers;
    $this->content = $content;
  }

  public function header($name, $value): self
  {
    $this->headers[$name] = $value;
    return $this;
  }

  public function send(): void
  {
    if (!headers_sent()) {
      http_response_code($this->status);

      foreach ($this->headers as $name => $value) {
        header("$name: $value");
      }
    }

    echo $this->content;
    exit;
  }
}