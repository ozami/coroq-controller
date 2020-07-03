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

  public function testDeadEnd() {
    $router = new Router(["first", "" => "root", "last"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals([], $result);
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
    $this->assertEquals([], $result);
  }
}
