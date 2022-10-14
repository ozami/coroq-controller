<?php
namespace Coroq\Controller;
use Psr\Http\Message\ResponseInterface as Response;

class ResponseEmitter {
  public function emit(Response $response) {
    foreach ($response->getHeaders() as $name => $values) {
      foreach ($values as $value) {
        header("$name: $value", false);
      }
    }
    http_response_code($response->getStatusCode());
    $body = $response->getBody();
    if ($body->isSeekable()) {
      $body->rewind();
    }
    while (!$body->eof()) {
      echo $body->read(1024 * 1024);
    }
  }
}
