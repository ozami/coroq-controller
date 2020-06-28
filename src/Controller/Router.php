<?php
namespace Coroq\Controller;

class Router {
  public function route(array $waypoints, array $map, $dead_end_action) {
    return $this->routeHelper([], $waypoints, $map, $dead_end_action);
  }

  protected function routeHelper(array $route, array $waypoints, array $map, $dead_end_action) {
    $next_waypoint = @$waypoints[0];
    foreach ($map as $map_index => $map_item) {
      if (preg_match('#^\d+$#', "$map_index")) {
        $route[] = $map_item;
        continue;
      }
      if (preg_match("|^[/#]|", "$map_index")) {
        if (preg_match($map_index, $next_waypoint)) {
          return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item, $dead_end_action);
        }
        continue;
      }
      if ($map_index == $next_waypoint) {
        return $this->routeHelper($route, array_slice($waypoints, 1), (array)$map_item, $dead_end_action);
      }
    }
    if ($waypoints) {
      return [$dead_end_action];
    }
    return $route;
  }
}
