<?php
namespace Coroq\Controller;
use Psr\Http\Message\RequestInterface as Request;

class RequestRewriter {
  /** @var array */
  protected $rules;

  public function __construct(array $rules) {
    $this->rules = $rules;
  }

  public function rewrite($request): Request {
    foreach ($this->rules as $rule) {
      $request = $rule->rewrite($request);
    }
    return $request;
  }
}
