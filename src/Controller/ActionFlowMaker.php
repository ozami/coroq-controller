<?php
namespace Coroq\Controller;
use Coroq\Flow;

class ActionFlowMaker {
  public function make(array $action_names): Flow {
    if (!$action_names) {
      return (new Flow())->to(function(\Closure $notFound) {
        return $notFound();
      });
    }
    $flow = new Flow();
    foreach ($action_names as $action_name) {
      $flow->to($this->instantiate($action_name));
    }
    return $flow;
  }

  protected function instantiate($action_name) {
    return $action_name;
  }
}
