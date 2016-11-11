<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Students Per Course');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('Course') . ":</td>
		<td><select name='course'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Course');
		$sql="SELECT id,course_name FROM courses ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['course_name'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr></table>';
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Display Students') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	
if (isset($_POST['submit'])) {
$_SESSION['course']=$_POST['course'];
echo '<table border="1">';
	
echo '<tr><td>' . _('Search Student RegNo/Name') . ':<input type="Text" name="searchval" 
  size=30   maxlength=20></td>
		<td><input  type="submit" name="form1" value="Search"></td></tr>';
	
    echo '<tr><th>' . _('View') . ':</th>
		<th>' . _('Bill') . ':</th>
		<th>' . _('Edit') . ':</th>
		<th>' . _('Name') . ':</th>
		<th>' . _('RegNo') . ':</th>';
		
  if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT count(*) FROM debtorsmaster";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "StudentPerCourseReport.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';
if (isset($_POST['form1'])){
$sql = "SELECT * FROM debtorsmaster
WHERE debtorno LIKE  '". $SearchString."'
OR name LIKE  '". $SearchString."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);		
}		
else{
$sql = "SELECT COUNT(debtorno) FROM debtorsmaster
WHERE course_id='".$_SESSION['course']."'";
$result = DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$no_of_students=$myrow[0];

$sql = "SELECT * FROM debtorsmaster
WHERE course_id='".$_SESSION['course']."'
ORDER BY debtorno $limit";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}			
			while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
			$ovamount=-$row['ovamount']; ?>
			<?php 
			echo '<td><a  href="' .'StudentStatements.php? &debtorno=' . $row['debtorno'].'">'._('View Statement').'</a></td>';
			echo '<td><a  href="' .'StudentBilling.php? &debtorno=' . $row['debtorno'].'">'._('Bill Student').'</a></td>';
			echo '<td><a href="' . $rootpath . '/Students.php?&DebtorNo=' . $row['debtorno'] . '">' . _('Edit Student') . '</a></td>';
			?><?php
		  echo "<td>".$row['name']."</td>";
		  echo "<td>".$row['debtorno']."</td>";
		  
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
   echo " NEXT LAST "."</td><td>".$no_of_students._(' ')._('Students')."</td></tr>";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage'>NEXT</a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>LAST</a> ";
}
			
include('includes/footer.inc');
}	
include('includes/footer.inc');
?>
