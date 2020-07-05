<?php
namespace Coroq\Controller\RequestRewriter;
use Psr\Http\Message\ServerRequestInterface as Request;

class PathToQueryPreg {
  /** @var string */
  protected $pattern;
  /** @var callable */
  protected $callback;

  public function __construct(string $pattern, callable $callback) {
    $this->pattern = $pattern;
    $this->callback = $callback;
  }

  public function rewrite(Request $request): Request {
    if (!preg_match($this->pattern, $request->getUri()->getPath(), $matches)) {
      return $request;
    }
    list($path, $query) = call_user_func_array($this->callback, $matches);
    $request = $request->withUri($request->getUri()->withPath($path));
    $request = $request->withQueryParams($query + $request->getQueryParams());
    return $request;
  }
}
