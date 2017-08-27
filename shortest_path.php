<?php

	$map_data = array(
		array(0,0,0,0,0,0,0,0,0,0),
		array(0,1,0,0,0,0,0,2,0,0),
		array(0,0,1,0,0,0,0,0,0,0),
		array(0,0,0,1,0,0,0,0,0,0),
		array(0,0,0,0,1,0,0,0,0,0),
		array(0,0,0,0,0,1,0,0,0,0),
		array(0,0,0,0,0,0,1,0,0,0),
		array(0,3,0,0,0,0,0,1,0,0),
		array(0,0,0,0,0,0,0,0,1,0),
		array(0,0,0,0,0,0,0,0,0,0)
	);

	// Start from 2, end with 3
	// 1 = non movable
	// Known coordinates from matrix
	$start = "7,1"; // x, y
	$end = "1,7";   // x, y

	echo 'Start coords [' . $start . ']' . "\n";
	echo 'End coords   [' . $end . ']' . "\n";

	// Assuming 4 directions: up, down, left, right
	// Assuming the distance of each movement is the same
	// Assuming the distance value is 1
	// Assuming you don't walk back to visited node
	// Only handling square map

	class Node {
		public $coord; // x, y
		public $prev;
		public $from_direction;
		public $distance;

		function __construct() {
			$this->coord = '';
			$this->prev = NULL;
			$this->from_direction = -1;
			$this->distance = 0;
		}
	}

	class PathFinder {
		const _UP = 0;
		const _DOWN = 1;
		const _RIGHT = 2;
		const _LEFT = 3;

		private $origin_coord;
		private $destination_coord;
		private $map_data;
		private $counter; // fail-safe for do-while
		private $shortest_path_index;

		private $lists;
		private $lists_removed; // Array of removed list within do-while loop

		function __construct($map_data, $origin, $destination) {
			$this->map_data = $map_data;
			// Note : Assuming we already know the origin and destination
			$this->origin_coord = $origin;
			$this->destination_coord = $destination;
			$this->shortest_path_index = -1;

			$this->lists = array();
			$this->lists_removed = array();
		} // __construct

		private function _getMapValue($coord) {
			// Use coord string to get map value
			if ( $coord ) {
				$coord = explode(",", $coord);
				$coord = array_reverse($coord); // Flip x, y back to multi dimensional array x,y => i,j
				// Check boundary
				if ( intval($coord[0]) >= 0 &&
						intval($coord[0]) <= count($this->map_data[0]) &&
						intval($coord[1]) >= 0 &&
						intval($coord[1]) <= count($this->map_data) ) {
					return $this->map_data[trim($coord[0])][trim($coord[1])];
				}
			}
			return -1;
		} // _getMapValue

		private function _isReachable($coord) {
			$can_reach = false;
			switch ($this->_getMapValue($coord)) {
				case 0:
				case 3:
					$can_reach = true;
					break;
				case 1:
				default:
					$can_reach = false;
			}
			return $can_reach;
		} // _isMovable

		private function _isDestination($coord) {
			if ( $this->_getMapValue($coord) === 3 ) {
				return true;
			}
			return false;
		}

		private function _processList($process_existing_path, $path, $node) {
			if ( !$path ) {
				// Init path
				array_push($this->lists, array(
					$node->coord => $node
				));

				return count($this->lists) - 1;
			} else if ( $process_existing_path != -1  ) {
				// Add node to existing path
				$this->lists[$process_existing_path][$node->coord] = $node;
				return $process_existing_path;
			} else if ( count($path[1]) > 0 ) {
				// Add node to new path
				// Before adding new node, check if already crossed path with other node
				// Clone from originated path
				$new_path = array();
				foreach ($path[1] as $k => $v) {
					$new_path[$k] = clone $v;
				}
				$new_path[$node->coord] = $node;
				array_push($this->lists, $new_path);

				return count($this->lists) - 1;
			}
		} // function processList

		private function _processCrossedPaths($path_index, $node) {
			$coord_to_check = $node->coord;
			foreach ( $this->lists as $idx => $row ) {
				if ( $idx != $path_index ) {
					if ( $this->lists[$idx][$coord_to_check] != '' ) {
						// Compare distance, may the shortest wins
						if ( $this->lists[$idx][$coord_to_check]->distance <= $node->distance ) {
							// Remove path from list
							return false;
						} else if ( $this->lists[$idx][$coord_to_check]->distance >= $node->distance ) {
							// Remove path from list
							array_push($this->lists_removed, $idx);
							array_splice($this->lists, $idx, 1);
						}
					}
				}
			}

			return true;
		} // function _processCrossedPaths

		private function _isVisited($path_index, $coord) {
			// Check if already been to the node on the same path
			return $this->lists[$path_index][$coord] ? true : false;
		}

		private function _move($direction, $coord, $from_node = NULL) {
			$new_coord = '';
			$current_coord = explode(",", $coord);

			// Check if next coord reachable, already visited, and if moving backwards
			switch ( $direction ) {
				case $this::_UP:
					$next_coord = $current_coord[0] . "," . (intval($current_coord[1]) - 1);
					if ( (!$from_node || $from_node->from_direction != $this::_DOWN) &&
							$this->_isReachable($next_coord) ) {
						$new_coord = $next_coord;
					}
					break;
				case $this::_RIGHT:
					$next_coord = (intval($current_coord[0] + 1)) . "," . $current_coord[1];
					if ( (!$from_node || $from_node->from_direction != $this::_LEFT) &&
							$this->_isReachable($next_coord) ) {
						$new_coord = $next_coord;
					}
					break;
				case $this::_DOWN:
					$next_coord = $current_coord[0] . "," . (intval($current_coord[1]) + 1);
					if ( (!$from_node || $from_node->from_direction != $this::_UP) &&
							$this->_isReachable($next_coord) ) {
						$new_coord = $next_coord;
					}
					break;
				case $this::_LEFT:
					$next_coord = (intval($current_coord[0]) - 1) . "," . $current_coord[1];
					if ( (!$from_node || $from_node->from_direction != $this::_RIGHT) &&
							$this->_isReachable($next_coord) ) {
						$new_coord = $next_coord;
					}
					break;
			}

			if ( $new_coord != '' ) {
				$node = new Node();
				$node->from_direction = $direction; // Prevent moving backwards
				$node->prev = $from_node->coord;
				$node->distance = !$from_node ? 1 : $from_node->distance + 1;
				$node->coord = $new_coord;
				return $node;
			} else return NULL;
		} // _move

		private function _checkCounter() {
			return ($this->counter > (count($this->map_data) * count($this->map_data[0]))) ? true : false;
		}

		private function _plotMap() {

		} // function _plotMap

		private function _showMap() {

		} // function _showMap

		public function start() {
			// Check three sides and see if isMovable unless is first move
			// Note : 3 instead of 4 sides because we don't trace back
			// Note : When check movable direction rotate clockwise (from noon)

			$end = false;

			// First moves
			for ( $dir = 0 ; $dir < 4 ; $dir++ ) {
				$next_node = $this->_move($dir, $this->origin_coord, NULL);
				$this->_processList(false, NULL, $next_node);
			} // for ( $i direction )

			do {
				$this->lists_removed = array();

				foreach ( $this->lists as $path_index => $row ) {
					if ( in_array($path_index, $this->lists_removed) ) continue;

					// Process 3 directions based on moving direction
					$current_node = end($row);
					$existing_path = $path_index;

					for ( $dir = 0 ; $dir < 4 ; $dir++ ) {
						$next_node = $this->_move($dir, $current_node->coord, $current_node);
						if ( $next_node->coord &&
								!$this->_isVisited($path_index, $next_node->coord) &&
								$this->_processCrossedPaths($path_index, $next_node) ) {
							$processed_path_index = $this->_processList($existing_path, array($path_index, $row), $next_node);
							$existing_path = -1;

							if ( $this->_isDestination($next_node->coord) ) {
								$this->shortest_path_index = $processed_path_index;
								$end = true;
								break;
							}
						}
					} // for ( $i direction )
					if ( $end ) {
						break;
					}
				} // foreach ( $lists )

				$this->counter++;
				//$end = $end ? true : $this->_checkCounter();
			} while ( $end !== true );
		}

		public function showWinner() {
			if ( $this->shortest_path_index >= 0 ) {
				print_r($this->lists[$this->shortest_path_index]);
				echo "\n=========================\n";
				echo " Paths count: " . count($this->lists) . "\n";
				echo " Shortest path index: " . $this->shortest_path_index . "\n";
				echo " Do-while loop count: " . $this->counter . "\n";
				echo "=========================\n";
			} else {
				echo "Shortest path not found\n";
			}
		}

		public function showMap($winner = flag) {
			$map = $this->map_data;
			if ( $winner ) {
				array_pop($this->lists[$this->shortest_path_index]);
				$shortest_path = $this->lists[$this->shortest_path_index];
				foreach ( $shortest_path as $idx => $row ) {
					$coord = array_reverse(explode(',',$row->coord)); // Flip x, y back to multi dimensional array x,y => i,j
					$map[$coord[0]][$coord[1]] = 'X';
				}
			} // if
			echo "\n";
			for ( $i = 0 ; $i < count($map) ; $i++ ) { // Y
				echo " ";
				for ( $j = 0 ; $j < count($this->map_data[$i]) ; $j++ ) { // X
					echo $this->map_data[$i][$j];
				} // for $j
				echo "   ";
				for ( $j = 0 ; $j < count($map[$i]) ; $j++ ) { // X
					echo $map[$i][$j];
				} // for $j
				echo "\n";
			} // for $i
			echo "\n";
		} // function showMap
	} // class PathFinder

	$path = new PathFinder($map_data, $start, $end);
	$path->start();
	$path->showWinner();
	$path->showMap();

	// Thoughts:
	// - How to process very large map? - Break it into sections?
	// - Take into account if this is a maze, handle dead-ends
?>
