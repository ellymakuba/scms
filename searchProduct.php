<?php
include('includes/session.inc');
$q=$_GET['term'];
$sql = "SELECT id,description FROM stockmaster WHERE description Like '%".$q."%'";
$obj = array();
$result=DB_query($sql,$db);
while($row=DB_fetch_assoc($result))
{
 $obj[]=array('stockid' => $row['id'], 'description' => $row['description']);
}
print json_encode($obj);
?>
