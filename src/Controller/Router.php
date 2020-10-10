<?php
namespace Coroq\Controller;
use Psr\Http\Message\RequestInterface as Request;

class Router {
  /** @var array */
  protected $map;

  public function __construct(array $map) {
    $this->map = $map;
  }

  public function route(Request $request): array {
    $waypoints = $this->getWaypoints($request);
    return $this->routeHelper([], $waypoints, $this->map);
  }

  protected function getWaypoints(Request $request): array {
    $path = $request->getUri()->getPath();
    $waypoints = array_diff(explode("/", ltrim($path, "/")), [""]);
    return $waypoints;
  }

  protected function routeHelper(array $route, array $waypoints, array $map): array {
    $next_waypoint = @$waypoints[0];
    $default_class_name = "";
    foreach ($map as $map_index => $map_item) {
      if (preg_match('#^\d+$#', "$map_index")) {
        if (is_string($map_item) && preg_match('#(.+)::$#', $map_item, $matches)) {
          $default_class_name = $matches[1];
          continue;
        }
        $route[] = $this->resolveDefaultClassName($default_class_name, $map_item);
        continue;
      }
      if (preg_match("|^[/#]|", "$map_index")) {
        if (preg_match($map_index, $next_waypoint)) {
          return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item);
        }
        continue;
      }
      if ($map_index == $next_waypoint) {
        if (is_array($map_item)) {
          return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item);
        }
        if (preg_match('#::$#', $map_item)) {
          $map_item .= $map_index;
        }
        $route[] = $this->resolveDefaultClassName($default_class_name, $map_item);
        return $route;
      }
    }
    if ($waypoints) {
      return [];
    }
    return $route;
  }

  protected function resolveDefaultClassName($default_class_name, $map_item) {
    if (is_string($map_item)) {
      if (substr($map_item, 0, 2) == "::") {
        $map_item = "$default_class_name$map_item";
      }
    }
    return $map_item;
  }
}
