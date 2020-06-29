<?php
use Coroq\Controller\Dispatcher;
use Coroq\FlowFunction;
use Laminas\Diactoros\Response;

/**
 * @covers Coroq\Controller\Dispatcher
 */
class DispatcherTest extends \PHPUnit\Framework\TestCase {
  public function testOk() {
    $dispatcher = new Dispatcher();
    $action = FlowFunction::make(function($ok) {
      return $ok();
    });
    $arguments = ["response" => (new Response())->withStatus(100)];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(200, $result["response"]->getStatusCode());
  }

  public function testOkJson() {
    $dispatcher = new Dispatcher();
    $data = ["result" => "ok", "data" => "hello"];
    $action = FlowFunction::make(function($okJson) use ($data) {
      return $okJson($data);
    });
    $arguments = ["response" => (new Response())->withStatus(100)];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(200, $result["response"]->getStatusCode());
    $this->assertEquals("application/json", $result["response"]->getHeaderLine("Content-Type"));
    $this->assertEquals(json_encode($data), $result["response"]->getBody());
  }

  public function testFound() {
    $dispatcher = new Dispatcher();
    $action = FlowFunction::make(function($found) {
      return $found("/somewhere");
    });
    $arguments = ["response" => new Response()];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(301, $result["response"]->getStatusCode());
  }

  public function testNotFound() {
    $dispatcher = new Dispatcher();
    $action = FlowFunction::make(function($notFound) {
      return $notFound();
    });
    $arguments = ["response" => new Response()];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(404, $result["response"]->getStatusCode());
  }

  public function testForbidden() {
    $dispatcher = new Dispatcher();
    $action = FlowFunction::make(function($forbidden) {
      return $forbidden();
    });
    $arguments = ["response" => new Response()];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(403, $result["response"]->getStatusCode());
  }

  public function testServiceUnavailable() {
    $dispatcher = new Dispatcher();
    $action = FlowFunction::make(function($serviceUnavailable) {
      return $serviceUnavailable();
    });
    $arguments = ["response" => new Response()];
    $result = $dispatcher->dispatch($action, $arguments);
    $this->assertEquals(503, $result["response"]->getStatusCode());
  }
}
