<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Manage Students');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('Class') . ': </td><td><select tabindex="5" name="grade_level_id">';
$result = DB_query('SELECT * FROM gradelevels',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['grade_level_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop
	echo '</select></td></tr></table>';
	
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Search Subjects') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	

if (isset($_POST['submit'])) {
$_SESSION['student_class'] = $_POST['grade_level_id'];
	if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}


$sql = "SELECT count(*) FROM subjects";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "ScheduleCourses.php";
$rows_per_page = 2;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';

if (isset($_POST['grade_level_id']) && $_POST['grade_level_id'] !=0) {
$sql = "SELECT COUNT(*) FROM subjects";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]>0 ){

echo '<tr><td>' . _('Period') . ":</td>
		<td><select name='period_id'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Period');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';
		
	
		
echo '<tr><th>' . _('Add Subject') . '</th>
		<th>' . _('Subject Name') . ':</th>';
		
		$sql = "SELECT * FROM subjects";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		
		while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
			$ovamount=-$row['ovamount']; ?>
			<?php 
		echo "<tr><td><Input type = 'Checkbox' name ='add_id[]' value='".$row['id']."'>".$row['id']."</td>";
			?><?php
		  echo "<td>".$row['subject_name']."</td>";
		  
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
echo "<br><div class='centre'><input  type='Submit' name='register' value='" . _('Schedule') . "'></div>";
		}
		else{
prnMsg( _('There are no records to display for the chosen program'),'error');
}	
}				
else{
prnMsg( _('Please select a program'),'error');
}			
			
}
if (isset($_POST['register'])){	
$_SESSION['period'] = $_POST['period_id'];
$_SESSION['grade_level'] = $_POST['grade_level_id'];
$i=0;
if(isset($_POST['add_id'])){
foreach($_POST['add_id'] as $value){
$sql = "SELECT id FROM scheduled_courses
		WHERE course_id='$value'
		AND grade_level_id='". $_SESSION['student_class'] ."'";
		$result=DB_query($sql,$db);
if(DB_fetch_row($result)>0){
prnMsg(_($value._(' ').'has already been scheduled for this Class'),'warn');
$i++;		
}
else{
$sql = "INSERT INTO scheduled_courses (course_id,term_id,grade_level_id) 
		VALUES ('$value','" .$_SESSION['period'] ."','" .$_SESSION['student_class'] ."') ";
		$ErrMsg = _('The subjects could not be updated because');
$result = DB_query($sql,$db,$ErrMsg);
$i++;
prnMsg( _('Subjects scheduling successful'),'success');		
}
}
}
include('includes/footer.inc');
			exit;
}	
include('includes/footer.inc');
?>
