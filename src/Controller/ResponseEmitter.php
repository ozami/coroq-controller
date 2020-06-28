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
    echo $response->getBody();
  }
}
