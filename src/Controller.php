<?php
namespace Coroq;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface as Logger;

class Controller {
  /** @var string */
  public $request_index;
  /** @var string */
  public $response_index;
  /** @var Request */
  public $request;
  /** @var Response */
  public $response;
  public $request_rewriter;
  /** @var array */
  public $request_rewrite_rules;
  public $router;
  /** @var array */
  public $routing_map;
  public $action_flow_maker;
  public $dispatcher;
  public $response_emitter;
  /** @var Logger */
  protected $logger;

  public function __construct() {
    $this->request_rewrite_rules = [];
    $this->routing_map = [];
    $this->request_index = "request";
    $this->response_index = "response";
 }

  public function __invoke(array $arguments): array {
    $request = $this->makeRequest();
    $this->logDebug("Request", [
      "method" => $request->getMethod(),
      "uri" => (string)$request->getUri(),
      "headers" => $request->getHeaders(),
      "body_length" => $request->getBody()->getSize(),
    ]);
    $request_rewriter = $this->makeRequestRewriter();
    $request = $request_rewriter->rewrite($request);
    $this->logDebug("Rewritten request", [
      "method" => $request->getMethod(),
      "uri" => (string)$request->getUri(),
      "headers" => $request->getHeaders(),
      "body_length" => $request->getBody()->getSize(),
    ]);
    $router = $this->makeRouter();
    $route = $router->route($request);
    $this->logDebug("Route", compact("route"));
    $action_flow_maker = $this->makeActionFlowMaker();
    $action_flow = $action_flow_maker->make($route);
    $arguments[$this->request_index] = $request;
    $arguments[$this->response_index] = $this->makeResponse();
    $dispatcher = $this->makeDispatcher();
    $arguments = $dispatcher->dispatch($action_flow, $arguments) + $arguments;
    $response = $arguments[$this->response_index];
    $this->logDebug("Response", [
      "status_code" => $response->getStatusCode(),
      "headers" => $response->getHeaders(),
      "body_length" => $response->getBody()->getSize(),
    ]);
    $response_emitter = $this->makeResponseEmitter();
    $response_emitter->emit($response);
    return $arguments;
  }

  protected function makeRequest(): Request {
    return $this->request ?: \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
  }

  protected function makeResponse(): Response {
    return $this->response ?: new \Laminas\Diactoros\Response();
  }

  protected function makeRequestRewriter() {
    return $this->request_rewriter ?: new Controller\RequestRewriter($this->request_rewrite_rules);
  }

  protected function makeRouter() {
    return $this->router ?: new Controller\Router($this->routing_map);
  }

  protected function makeActionFlowMaker() {
    return $this->action_flow_maker ?: new Controller\ActionFlowMaker();
  }

  protected function makeDispatcher() {
    return $this->dispatcher ?: new Controller\Dispatcher($this->response_index);
  }

  protected function makeResponseEmitter() {
    return $this->response_emitter ?: new Controller\ResponseEmitter();
  }

  public function setLogger(Logger $logger): void {
    $this->logger = $logger;
  }

  protected function logDebug($message, array $context = []): void {
    if ($this->logger) {
      $this->logger->debug($message, $context);
    }
  }
}
