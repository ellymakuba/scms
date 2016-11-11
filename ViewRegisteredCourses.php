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
$sql = "SELECT id FROM debtorsmaster
		WHERE debtorno= '". $_SESSION['UserID'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$student_id=$myrow[0];
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('Choose Semester') . ":</td>
		<td><select name='semester'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Semester');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';	
		
echo '<tr><td class="visible">' . _('Registration Type') . ":</td>
		<td class=\"visible\"><select name='registration_type'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Registration Type');
		$sql="SELECT * FROM registration_types ORDER BY name";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['name'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr></table>';			
		
		
		
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Display Courses') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	

if (isset($_POST['submit'])) {
$_SESSION['semester'] = $_POST['semester'];
$_SESSION['registration'] = $_POST['registration_type'];
	if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}


$sql = "SELECT count(*) FROM subjects";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "ViewRegisteredCourses.php";
$rows_per_page = 2;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';

if (isset($_POST['semester']) && $_POST['semester'] !=0
&& isset($_POST['registration_type']) && $_POST['registration_type'] !=0) {
$sql = "SELECT name FROM registration_types
		WHERE id='". $_SESSION['registration'] ."'";
        $result=DB_query($sql,$db);
		$row=DB_fetch_row($result);
		$registration=$row[0];

		
$sql = "SELECT COUNT(*) FROM registered_students
		WHERE student_id =  '" . $student_id . "'
		AND period_id='". $_SESSION['semester'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]>0 ){
		
$sql = "SELECT title FROM terms
		WHERE id='". $_SESSION['semester'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$semester_name=	$myrow[0];	
		$year=$_SESSION['year'];
		
$sql = "SELECT y.year FROM years y
	INNER JOIN collegeperiods cp ON cp.year=y.id
	WHERE cp.id='". $_SESSION['semester'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$year_name=	$myrow[0];	
		
	echo '<br><table width="50%">';	
	echo '<tr><td>' . _('Semester') . ":</td>
		<td>".$semester_name.' '.$year_name."</td><td>". _('Registration Type :') .' '.$registration."</td></tr>";
		
echo '<tr><th>' . _('No') . '</th>
	<th>' . _('Course Code') . ':</th>
		<th>' . _('Course Name') . ':</th>';
if($_SESSION['registration']==4){
$sql = "SELECT rs.*,sub.subject_code,sub.subject_name FROM registered_retake_students rs
		INNER JOIN subjects sub ON sub.id=rs.subject_id
		WHERE rs.student_id =  '" . $student_id. "'
		AND rs.period_id='". $_SESSION['semester'] ."'";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}
elseif($_SESSION['registration']==5){
$sql = "SELECT rs.*,sub.subject_code,sub.subject_name FROM registered_supplimentary_students rs
		INNER JOIN subjects sub ON sub.id=rs.subject_id
		WHERE rs.student_id =  '" . $student_id. "'
		AND rs.period_id='". $_SESSION['semester'] ."'";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}
else{		
		$sql = "SELECT rs.*,sub.subject_code,sub.subject_name FROM registered_students rs
		INNER JOIN subjects sub ON sub.id=rs.subject_id
		WHERE rs.student_id =  '" . $student_id. "'
		AND period_id='". $_SESSION['semester'] ."'";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}		
		$course_counter=0;
		while ($row = DB_fetch_array($result))
			{
			$course_counter++;
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
		echo "<tr><td class=\"visible\">". $course_counter."</td>";
		echo "<td class=\"visible\" align=\"center\">". $row['subject_code']."</td>";
		  echo "<td class=\"visible\">".$row['subject_name']."</td>";
		  
		    echo "</tr>";
		  $j++;
			}
			

	}
		else{
prnMsg( _('There are no courses to display for the chosen Semester'),'error');
}	
}				
else{
prnMsg( _('Please select Semester to view your courses'),'error');
}			
include('includes/footer.inc');
			exit;			
}
	
include('includes/footer.inc');
?>
