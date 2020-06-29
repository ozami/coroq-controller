<?php
namespace Coroq;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Controller {
  protected $map;
  /** @var string */
  protected $request_index;
  /** @var string */
  protected $response_index;
  /** @var Request */
  protected $request;
  /** @var Response */
  protected $response;
  protected $router;
  protected $action_flow_maker;
  protected $dispatcher;
  protected $response_emitter;

  public function __construct(array $map, array $options = []) {
    $this->map = $map;
    $this->request_index = @$options["request_index"] ?: "request";
    $this->response_index = @$options["response_index"] ?: "response";
    $this->request = @$options["request"] ?: $this->makeRequest();
    $this->response = @$options["response"] ?: $this->makeResponse();
    $this->router = @$options["router"] ?: $this->makeRouter();
    $this->action_flow_maker = @$options["action_flow_maker"] ?: $this->makeActionFlowMaker();
    $this->dispatcher = $options["dispatcher"] ?: $this->makeDispatcher();
    $this->response_emitter = $options["response_emitter"] ?: $this->makeResponseEmitter();
  }

  public function __invoke(array $arguments): array {
    $route = $this->router->route($this->request, $this->map);
    $action_flow = $this->action_flow_maker->make($route);
    $arguments[$this->request_index] = $this->request;
    $arguments[$this->response_index] = $this->response;
    $arguments = $this->dispatcher->dispatch($action_flow, $arguments) + $arguments;
    $this->response_emitter->emit($arguments[$this->response_index]);
    return $arguments;
  }

  protected function makeRequest(): Request {
    return \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
  }

  protected function makeResponse(): Response {
    return new \Laminas\Diactoros\Response();
  }

  protected function makeRouter() {
    return new Controller\Router();
  }

  protected function makeActionFlowMaker() {
    return new Controller\ActionFlowMaker();
  }

  protected function makeDispatcher() {
    return new Controller\Dispatcher($this->response_index);
  }

  protected function makeResponseEmitter() {
    return new Controller\ResponseEmitter();
  }
}
