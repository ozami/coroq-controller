<?php
namespace Coroq\Controller;
use Psr\Http\Message\StreamInterface;

class Dispatcher {
  public function dispatch(\Coroq\Flow $action_flow, array $arguments) {
    try {
      return $action_flow($arguments + $this->makeResponseFunctions($arguments));
    }
    catch (\RuntimeException $exception) {
      return $this->handleRuntimeException($exception, $arguments);
    }
  }

  protected function handleRuntimeException(\RuntimeException $exception, array $arguments) {
    return $this->handleServiceUnavailable($arguments);
  }

  protected function makeResponseFunctions(array $arguments) {
    $ok = function(StreamInterface $body = null) use ($arguments) {
      return $this->handleOk($arguments, $body);
    };
    $okView = function($view_arguments = []) use ($arguments) {
      return $this->handleOkView($arguments, $view_arguments);
    };
    $okJson = function($data) use ($arguments) {
      return $this->handleOkJson($arguments, $data);
    };
    $found = function($url, array $query = [], $fragment = null) use ($arguments) {
      return $this->handleFound($arguments, $url, $query, $fragment);
    };
    $forbidden = function() use ($arguments) {
      return $this->handleForbidden($arguments);
    };
    $notFound = function() use ($arguments) {
      return $this->handleNotFound($arguments);
    };
    $serviceUnavailable = function() use ($arguments) {
      return $this->handleServiceUnavailable($arguments);
    };
    return compact(
      "ok",
      "okView",
      "okJson",
      "found",
      "notFound",
      "forbidden",
      "serviceUnavailable"
    );
  }

  protected function getResponse(array $arguments) {
    return $arguments["response"];
  }

  protected function handleOk(array $arguments, StreamInterface $body = null) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(200);
    if ($body) {
      $response = $response->withBody($body);
    }
    return compact("response");
  }

  protected function handleOkView(array $arguments, array $view_arguments = []) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(200);
    // TODO
    return compact("response");
  }

  protected function handleOkJson(array $arguments, $data) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(200);
    $response->getBody()->write($this->encodeJsonBody($data));
    return compact("response");
  }

  protected function encodeJsonBody($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  protected function handleFound(array $arguments, $url, array $query = [], $fragment = null) {
    if ($query) {
      $url .= "?" . http_build_query($query);
    }
    if ($fragment !== null) {
      $url .= "#$fragment";
    }
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(301)->withHeader("Location", $url);
    return compact("response");
  }

  protected function handleForbidden(array $arguments) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(403);
    return compact("response");
  }

  protected function handleNotFound(array $arguments) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(404);
    return compact("response");
  }

  protected function handleServiceUnavailable(array $arguments) {
    $response = $this->getResponse($arguments);
    $response = $response->withStatus(503);
    return compact("response");
  }
}
