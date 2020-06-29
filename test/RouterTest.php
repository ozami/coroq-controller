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
    $router = new Router();
    $result = $router->route($this->makeRequest(""), ["" => "root"]);
    $this->assertEquals(["root"], $result);
  }

  public function testNamedMapItem() {
    $router = new Router();
    $result = $router->route($this->makeRequest("/abc"), ["" => "root", "abc" => "ABC"]);
    $this->assertEquals(["ABC"], $result);
  }

  public function testNumericMapIndex() {
    $router = new Router();
    $result = $router->route($this->makeRequest("/"), ["first", "" => "root", "last"]);
    $this->assertEquals(["first", "root"], $result);
  }

  public function testDeadEnd() {
    $router = new Router();
    $result = $router->route($this->makeRequest("/not_exists"), ["first", "" => "root", "last"]);
    $this->assertEquals([], $result);
  }

  public function testDeepMap() {
    $router = new Router();
    $map = [
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
    ];
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"), $map);
    $this->assertEquals(["1st", "2nd", "3rd", "4th", "5th", "6th", "last"], $result);
  }

  public function testDeepMapDeadEnd() {
    $router = new Router();
    $map = [
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
    ];
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"), $map);
    $this->assertEquals([], $result);
  }
}
