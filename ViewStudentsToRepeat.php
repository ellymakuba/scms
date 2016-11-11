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
	echo '<table>';
	
echo '<tr><td class="visible">' . _('Academic Year') . ":</td>
		<td class=\"visible\"><select name='academic_year'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Year');
		$result = DB_query('SELECT id,year FROM years',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['academic_year']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['year'];
} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr>';
echo '<tr><td class="visible">' . _('Class') . ":</td>
		<td class=\"visible\"><select name='class'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql = 'SELECT id,class_name FROM classes ORDER BY class_name';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['class']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
echo $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
echo '</select></td></tr></table>';	
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Display Students') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	
if (isset($_POST['submit'])) {
$_SESSION['class']=$_POST['class'];
echo '<br><table width="40%">';
	
$sql = "SELECT class_name FROM classes WHERE id='".$_POST['class']."'
 ";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$class_name = $query_data[0]; 

$sql = "SELECT year FROM years
 WHERE id='".$_POST['academic_year']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$year_name = $query_data[0]; 
 
 echo '<tr><td>' .$class_name. ':</td>
		<td></td>
		<td>' .$year_name  . ':</td></tr>';
echo '<tr><th>' . _('AdmsnNO') . ':</th>
		<th>' . _('Name') . ':</th>
		<th>' . _('Marks') . ':</th>
		';
		
  if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT count(*) FROM fails";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "ViewStudentsToRepeat.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';

$sql = "SELECT COUNT(f.id) FROM fails f
INNER JOIN debtorsmaster dm ON dm.id=f.student_id
WHERE period_id='".$_POST['academic_year']."'
AND dm.class_id='".$_SESSION['class']."'
ORDER BY f.marks";
$result = DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$no_of_students=$myrow[0];

$sql = "SELECT f.marks,dm.debtorno,dm.name FROM fails f
INNER JOIN debtorsmaster dm ON dm.id=f.student_id
WHERE period_id='".$_POST['academic_year']."'
AND dm.class_id='".$_SESSION['class']."'
ORDER BY f.marks";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
			while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
			
			echo '<td class="visible">' .  $row['debtorno'] . '</td>';
			echo '<td class="visible">' .  $row['name'] . '</td>';
			echo '<td class="visible">' . $row['marks']. '</td>';
			
		    echo "</tr>";
		  $j++;
			}
			

}	
include('includes/footer.inc');
?>
