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
    foreach ($map as $map_index => $map_item) {
      if (preg_match('#^\d+$#', "$map_index")) {
        $route[] = $map_item;
        continue;
      }
      if (preg_match("|^[/#]|", "$map_index")) {
        if (preg_match($map_index, $next_waypoint)) {
          return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item);
        }
        continue;
      }
      if ($map_index == $next_waypoint) {
        return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item);
      }
    }
    if ($waypoints) {
      return [];
    }
    return $route;
  }
}
