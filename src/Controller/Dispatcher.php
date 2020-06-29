<?php
namespace Coroq\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface;

class Dispatcher {
  /** @var string */
  protected $response_index;
  /** @var array */
  protected $arguments;

  public function __construct(string $response_index = "response") {
    $this->response_index = $response_index;
  }

  public function dispatch(callable $action_flow, array $arguments): array {
    try {
      $this->arguments = $arguments;
      $this->makeResponseFunctions();
      return \Coroq\FlowFunction::call($action_flow, $this->arguments);
    }
    catch (\RuntimeException $exception) {
      return $this->handleRuntimeException($exception);
    }
  }

  protected function getResponse(): Response {
    return $this->arguments[$this->response_index];
  }

  protected function setResponse(Response $response): array {
    return [$this->response_index => $response];
  }

  protected function handleRuntimeException(\RuntimeException $exception): array {
    return $this->handleServiceUnavailable();
  }

  protected function makeResponseFunctions(): void {
    $ok = function($body = null): array {
      return $this->handleOk($body);
    };
    $okJson = function($data): array {
      return $this->handleOkJson($data);
    };
    $found = function($url, array $query = [], $fragment = null): array {
      return $this->handleFound($url, $query, $fragment);
    };
    $forbidden = function(): array {
      return $this->handleForbidden();
    };
    $notFound = function(): array {
      return $this->handleNotFound();
    };
    $serviceUnavailable = function(): array {
      return $this->handleServiceUnavailable();
    };
    $this->arguments += compact(
      "ok",
      "okJson",
      "found",
      "notFound",
      "forbidden",
      "serviceUnavailable"
    );
  }

  protected function handleOk($body = null): array {
    $response = $this->getResponse();
    $response = $response->withStatus(200);
    if ($body !== null) {
      if ($body instanceof StreamInterface) {
        $response = $response->withBody($body);
      }
      elseif (is_string($body)) {
        $response->getBody()->write($body);
      }
      else {
        throw new \LogicException();
      }
    }
    return $this->setResponse($response);
  }

  protected function handleOkJson($data): array {
    $response = $this->getResponse();
    $response = $response->withStatus(200);
    $response = $response->withHeader("Content-Type", "application/json");
    $response->getBody()->write($this->encodeJsonBody($data));
    return $this->setResponse($response);
  }

  protected function encodeJsonBody($data): string {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  protected function handleFound($url, array $query = [], $fragment = null): array {
    if ($query) {
      $url .= "?" . http_build_query($query);
    }
    if ($fragment !== null) {
      $url .= "#$fragment";
    }
    $response = $this->getResponse();
    $response = $response->withStatus(301)->withHeader("Location", $url);
    return $this->setResponse($response);
  }

  protected function handleForbidden(): array {
    $response = $this->getResponse();
    $response = $response->withStatus(403);
    return $this->setResponse($response);
  }

  protected function handleNotFound(): array {
    $response = $this->getResponse();
    $response = $response->withStatus(404);
    return $this->setResponse($response);
  }

  protected function handleServiceUnavailable(): array {
    $response = $this->getResponse();
    $response = $response->withStatus(503);
    return $this->setResponse($response);
  }
}
