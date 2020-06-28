<?php
namespace Coroq;

class Controller {
  protected $map;
  protected $dead_end_action;

  public function __construct(array $map, $dead_end_action) {
    $this->map = $map;
    $this->dead_end_action = $dead_end_action;
  }

  public function __invoke(array $arguments) {
    $arguments = $this->makeRequest($arguments);
    $arguments = $this->makeResponse($arguments);
    $route = $this->route($arguments);
    $action_flow = $this->makeActionFlow($route, $arguments);
    $arguments = $this->dispatch($action_flow, $arguments);
    $this->emitResponse($arguments);
    return $arguments;
  }

  protected function getRequest(array $arguments) {
    return $arguments["request"];
  }

  protected function makeRequest(array $arguments) {
    $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
    return compact("request");
  }

  protected function getResponse(array $arguments) {
    return $arguments["response"];
  }

  protected function makeResponse(array $arguments) {
    $response = new \Laminas\Diactoros\Response();
    return compact("response");
  }

  protected function route(array $arguments) {
    $waypoints = $this->getRouteWaypoints($arguments);
    $router = $this->makeRouter($arguments);
    return $router->route($waypoints, $this->map, $this->dead_end_action);
  }

  protected function getRouteWaypoints(array $arguments) {
    $request = $this->getRequest($arguments);
    $path = $request->getPath();
    $waypoints = array_diff(explode("/", ltrim($path, "/")), [""]);
    return $waypoints;
  }

  protected function makeRouter(array $arguments) {
    return new Controller\Router();
  }

  protected function makeActionFlow($route, array $arguments) {
    $action_flow_maker = $this->makeActionFlowMaker($arguments);
    return $action_flow_maker->make($route);
  }

  protected function makeActionFlowMaker(array $arguments) {
    return new Controller\ActionFlowMaker();
  }

  protected function dispatch(Flow $action_flow, array $arguments) {
    $dispatcher = $this->makeDispatcher($arguments);
    return $dispatcher->dispatch($action_flow, $arguments);
  }

  protected function makeDispatcher(array $arguments) {
    return new Controller\Dispatcher();
  }

  protected function emitResponse(array $arguments) {
    $response = $this->getResponse($arguments);
    $emitter = $this->makeResponseEmitter($arguments);
    $emitter->emit($response);
  }

  protected function makeResponseEmitter(array $arguments) {
    return new Controller\ResponseEmitter();
  }
}
