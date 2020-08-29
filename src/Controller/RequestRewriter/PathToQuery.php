<?php
namespace Coroq\Controller\RequestRewriter;
use Psr\Http\Message\ServerRequestInterface as Request;

class PathToQuery {
  /** @var string */
  protected $format;

  public function __construct(string $format) {
    $this->format = $format;
  }

  public function rewrite(Request $request): Request {
    $path = $request->getUri()->getPath();
    $path = explode("/", ltrim($path, "/")); // assuming path is absolute
    $format = explode("/", ltrim($this->format, "/"));
    $new_path = [];
    $query = [];
    foreach ($format as $index => $format_item) {
      $path_item = @$path[$index];
      if ($path_item === null) {
        return $request;
      }
      if (preg_match('#^\{([a-z_][a-z0-9_]*)(:.+?)?\}$#i', $format_item, $matches)) {
        $query[$matches[1]] = urldecode($path_item);
        if (isset($matches[2])) {
          $new_path[] = substr($matches[2], 1);
        }
        continue;
      }
      if ($format_item === "" || $path_item == $format_item) {
        $new_path[] = $path_item;
        continue;
      }
      return $request;
    }
    $request = $request->withUri($request->getUri()->withPath("/" . join("/", $new_path)));
    $request = $request->withQueryParams($query + $request->getQueryParams());
    return $request;
  }
}
