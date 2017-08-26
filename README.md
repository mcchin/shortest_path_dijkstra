# Shortest path PHP assignment
Find shortest path from a multidimensional array (PHP)

Given the map as shown below:
```php
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
array(0,0,0,0,0,0,0,0,0,0));
```
* Start from value 2
* End on value 3
* Value 1 means the position is not accessible

Output example:

![Output example](https://github.com/mcchin/shortest_path_dijkstra/raw/master/example.jpg "Ouput example")

Clone the repository and run the following from terminal console to see the output
```
php shortest_path.php
```
