<?php
namespace Coroq\Controller\RequestRewriter;
use Psr\Http\Message\ServerRequestInterface as Request;

class ParseJsonBody {
  public function rewrite(Request $request): Request {
    $content_type = $request->getHeaderLine("content-type");
    if (!in_array($content_type, ["application/json"])) {
      return $request;
    }
    return $request->withParsedBody(json_decode((string)$request->getBody(), true));
  }
}
