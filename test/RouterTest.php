<?php
use Coroq\Controller\Router;

/**
 * @covers Coroq\Controller\Router
 */
class RouterTest extends \PHPUnit\Framework\TestCase {
  private function makeRequest($path) {
    return new \Laminas\Diactoros\Request($path);
  }

  public function testRoot() {
    $router = new Router(["" => "root"]);
    $result = $router->route($this->makeRequest(""));
    $this->assertEquals(["root"], $result);
  }

  public function testNamedMapItem() {
    $router = new Router(["" => "root", "abc" => "ABC"]);
    $result = $router->route($this->makeRequest("/abc"));
    $this->assertEquals(["ABC"], $result);
  }

  public function testNumericMapIndex() {
    $router = new Router(["first", "" => "root", "last"]);
    $result = $router->route($this->makeRequest("/"));
    $this->assertEquals(["first", "root"], $result);
  }

  public function testCatchAllOnly() {
    $router = new Router(["*"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterSomeRoute() {
    $router = new Router(["first", "*"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals(["first"], $result);
  }

  public function testCatchAllAfterDigging() {
    $router = new Router([
      "first" => [
        "*",
      ],
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterDiggingAndSomeRoute() {
    $router = new Router([
      "first" => [
        "second",
        "*",
      ],
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals(["second"], $result);
  }

  public function testCatchAllAfterDiggingAndDeadEnd() {
    $router = new Router([
      "first" => [
        "second",
      ],
      "*",
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterDiggingAndDeadEndAndSomeRoute() {
    $router = new Router([
      "first" => [
        "second",
      ],
      "third",
      "*",
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals(["third"], $result);
  }

  public function testDeadEnd() {
    $router = new Router(["first", "" => "root", "last"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals(null, $result);
  }

  public function testDeepMap() {
    $router = new Router([
      "1st",
      "2nd",
      "flower" => [
        "of" => "out",
      ],
      "leaf" => [
        "3rd",
        "of" => [
          "4th",
          "the" => [
            "5th",
            "tall" => [
              "6th",
              "tree" => "last",
              "out",
            ],
            "small" => [
              "tree" => "out",
            ],
          ],
        ],
      ],
      "out",
    ]);
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"));
    $this->assertEquals(["1st", "2nd", "3rd", "4th", "5th", "6th", "last"], $result);
  }

  public function testDeepMapDeadEnd() {
    $router = new Router([
      "1st",
      "2nd",
      "flower" => [
        "of" => "out",
      ],
      "leaf" => [
        "3rd",
        "of" => [
          "4th",
          "the" => [
            "5th",
            "tall" => [
              "6th",
              "wood" => "last",
              "out",
            ],
            "small" => [
              "tree" => "out",
            ],
          ],
        ],
      ],
      "out",
    ]);
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"));
    $this->assertEquals(null, $result);
  }

  public function testDefaultClassName() {
    $router = new Router([
      "::no_class_name",
      "abc" => [
        "SomeClass::",
        "::method1",
        "def" => "::method2",
      ],
    ]);
    $result = $router->route($this->makeRequest("/abc/def"));
    $this->assertEquals(["::no_class_name", "SomeClass::method1", "SomeClass::method2"], $result);
  }

  public function testDefaultMethodName() {
    $router = new Router([
      "::",
      "abc" => [
        "SomeClass::",
        "::",
        "def" => [
          "SomeClass::",
          "ghi" => "::"
        ],
      ],
    ]);
    $result = $router->route($this->makeRequest("/abc/def/ghi"));
    $this->assertEquals(["::", "SomeClass::", "SomeClass::ghi"], $result);
  }
}
