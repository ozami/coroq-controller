<?php
use Coroq\Controller\RequestRewriter\PathToQuery;

/**
 * @covers Coroq\Controller\RequestRewriter\PathToQuery
 */
class RewritePathToQueryTest extends \PHPUnit\Framework\TestCase {
  private function makeRequest($path) {
    return new \Laminas\Diactoros\ServerRequest([], [], new \Laminas\Diactoros\Uri($path));
  }

  public function testNotMatch() {
    $rule = new PathToQuery("/abc/{param}");
    $request = $this->makeRequest("/def/123");
    $this->assertEquals($request, $rule->rewrite($request));
  }

  public function testMatchAtBegining() {
    $rule = new PathToQuery("/{param}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/", $result->getUri()->getPath());
  }

  public function testMatchAtBeginingWithTrailingSlash() {
    $rule = new PathToQuery("/{param}/");
    $request = $this->makeRequest("/123/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/", $result->getUri()->getPath());
  }

  public function testMatchAtEnd() {
    $rule = new PathToQuery("/abc/def/{param}");
    $request = $this->makeRequest("/abc/def/123");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def", $result->getUri()->getPath());
  }

  public function testMatchAtEndWithTrailingSlash() {
    $rule = new PathToQuery("/abc/def/{param}/");
    $request = $this->makeRequest("/abc/def/123/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def/", $result->getUri()->getPath());
  }

  public function testMatchInMiddle() {
    $rule = new PathToQuery("/abc/{param}/def");
    $request = $this->makeRequest("/abc/123/def");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def", $result->getUri()->getPath());
  }

  public function testMatchInMiddleWithTrailingSlash() {
    $rule = new PathToQuery("/abc/{param}/def/");
    $request = $this->makeRequest("/abc/123/def/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def/", $result->getUri()->getPath());
  }
}
