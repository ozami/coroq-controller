<?php
namespace Coroq\Controller;
use Psr\Http\Message\RequestInterface as Request;

class Router {
  /** @var array */
  protected $map;

  public function __construct(array $map) {
    $this->map = $map;
  }

  public function route(Request $request): ?array {
    $waypoints = $this->getWaypoints($request);
    return $this->routeHelper([], $waypoints, $this->map, "");
  }

  protected function getWaypoints(Request $request): array {
    $path = $request->getUri()->getPath();
    $waypoints = array_diff(explode("/", ltrim($path, "/")), [""]);
    return $waypoints;
  }

  /**
   * @return ?array null if no route found.
   */
  protected function routeHelper(array $route, array $waypoints, array $map, string $default_class_name): ?array {
    $current_waypoint = array_shift($waypoints);
    if ($current_waypoint === null) {
      $current_waypoint = "";
    }
    foreach ($map as $map_index => $map_item) {
      if (is_int($map_index)) {
        if ($map_item == "*") {
          return $route;
        }
        if (is_string($map_item) && preg_match('#(.+)::$#', $map_item, $matches)) {
          $default_class_name = $matches[1];
          continue;
        }
        $route[] = $this->resolveDefaultClassName($default_class_name, $map_item);
        continue;
      }
      if ($this->doesWaypointMatchToMapIndex($current_waypoint, $map_index)) {
        if (is_array($map_item)) {
          $found_route = $this->routeHelper($route, $waypoints, $map_item, $default_class_name);
          if ($found_route === null) {
            continue;
          }
          return $found_route;
        }
        if ($waypoints) {
          return null;
        }
        if (is_string($map_item) && preg_match('#::$#', $map_item)) {
          $map_item .= $map_index;
        }
        $route[] = $this->resolveDefaultClassName($default_class_name, $map_item);
        return $route;
      }
    }
    return null;
  }

  private function doesWaypointMatchToMapIndex(string $waypoint, string $map_index): bool {
    // if $map_index is a regular expression
    if (preg_match("|^[/#]|", "$map_index") && preg_match($map_index, $waypoint)) {
      return true;
    }
    return $map_index === $waypoint;
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
