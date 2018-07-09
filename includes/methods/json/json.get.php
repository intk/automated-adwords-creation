<?php
header('Content-Type: application/json');
include('../../config.inc.php');

$array = array();
$query = mysqli_query($connect, "SELECT performances.id as Id, FROM_UNIXTIME(performances.creationDate, '%d-%m-%Y') as `Campaign creation date`, FROM_UNIXTIME(performances.performanceDate, '%d-%m-%Y') as `Performance date`, theaters.name as `Theater name`, theaters.location as `City`, performances.title as Title, performances.subtitle as Subtitle, performances.genre as Genre, performances.link as Link FROM performances JOIN theaters on theaters.id = performances.theaterId");
while($result = mysqli_fetch_assoc($query)) {
	array_push($array, $result);
}
echo json_encode($array);
?>