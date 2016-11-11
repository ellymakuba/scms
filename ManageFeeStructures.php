<?php
ob_start();
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Manage Fee Structures');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$sql = "SELECT fullaccess FROM www_users
WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$administrator_rights=$myrow[0];
$msg='';
?>
<html><body><br /><br /><br />
<table class="enclosed"><form name="manageinvoice" action="ManageFeeStructures.php" method="post">
<?php
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';
	
  echo '<tr><td>' . _('Search Fee Structure') . ':<input type="Text" name="searchval" 
  size=30   maxlength=20></td>
		<td><input  type="submit" name="form1" value="submit"></td></tr>';
echo '<tr>';
if($administrator_rights !=8){
echo '<th>'._('Action').'</th>';
}
echo '<th>'._('Class Fee Structure').'</th><th>'._('Year').'</th></tr>';   
  if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT count(*) FROM autobilling";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "ManageFeeStructures.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';
if (isset($_POST['form1'])){
$sql = "SELECT * FROM autobilling
WHERE class_id LIKE  '". $SearchString."'
OR id LIKE  '". $SearchString."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}				
else{
$sql = "SELECT ab.*,y.year,grade_level FROM autobilling ab
INNER JOIN years y ON y.id=ab.year_id
INNER JOIN gradelevels gl ON gl.id=ab.class_id
ORDER BY id DESC $limit";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}			
while ($row = DB_fetch_array($result))
{
	?>
	<td><?php 
	if($administrator_rights ==8){
	echo '<a  href="' . $rootpath . '/FeeStructure.php?feeStructure=' . $row['id'].'">'.$row['grade_level'].'</a>';
	}
	?></td><?php
	echo "<td>".$row['year']."</td>";		  
	echo "</tr>";
	$j++;
}
if ($pageno == 1) {
   echo "<tr><td>"." FIRST PREV ";
} else {
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=1'>FIRST</a> ";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage'>PREV</a> ";
}
echo " ( Page $pageno of $lastpage ) ";
if ($pageno == $lastpage) {
   echo " NEXT LAST "."</td></tr>";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage'>NEXT</a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>LAST</a> ";
}		
include('includes/footer.inc');
?>
