<?php
ob_start();
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Manage Students');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<form method='post' name='myform' action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br><table class="enclosed">';
echo '<tr><td>' . _('Period') . ":</td>
<td><select name='period_id'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Period');
$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
INNER JOIN terms ON terms.id=cp.term_id
INNER JOIN years ON years.id=cp.year ";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result))
{
	echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr>';
echo '<tr><td>' . _('Stream') . ': </td><td><select tabindex="5" name="student_class">';
$result = DB_query('SELECT * FROM classes',$db);
while ($myrow = DB_fetch_array($result)) 
{
	if ($myrow['id']==$_POST['class_name']) 
	{
	 echo '<option selected VALUE=';
	} 
	else 
	{
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['class_name'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr>';			
echo '<tr><td>' . _('Subjects') . ":</td><td><select name='subject_id'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Subjects');
$sql="SELECT sub.id,sub.subject_name,sub.subject_code FROM subjects sub
ORDER BY sub.subject_name";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result))
{
	echo '<option value='. $myrow['id'] . '>' . $myrow['subject_name'].' '._('(').$myrow['subject_code']._(')');
} //end while loop
echo '</select></td></tr></table>';		
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Display Students') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div><br>";
echo '<table class="enclosed">';
if (isset($_POST['submit'])) {

	if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT year FROM collegeperiods
WHERE id='".$_SESSION['semester']."'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_row($result);
$academic_year = $myrow[0];
$_SESSION['academic_year']=$academic_year;

$sql = "SELECT count(*) FROM debtorsmaster";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "DeRegisterStudents.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';

if (isset($_POST['subject_id']) && $_POST['subject_id'] !=0
  && isset($_POST['period_id']) && $_POST['period_id'] !=0
  && isset($_POST['student_class']) && $_POST['student_class'] !=0)
 {
 $_SESSION['subject'] = $_POST['subject_id'];
$_SESSION['semester'] = $_POST['period_id'];

$sql = "SELECT subject_name FROM subjects
		WHERE id= '". $_POST['subject_id'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$subject_name=$myrow[0];
echo '<tr><td>' . _('Subject') . ":</td>
		<td>".$subject_name."</td></tr>";

$sql = "SELECT class_name FROM classes
		WHERE id =  '". $_POST['student_class']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);	
echo "<tr><td>" . _('Class') . ":</td>
	<td>".$myrow[0]."</td></tr>";

$sql = "SELECT rs.id FROM registered_students rs
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		WHERE rs.subject_id= '". $_SESSION['subject'] ."'
		AND rs.period_id='".$_SESSION['semester']."'
		AND rs.class_id='".$_POST['student_class']."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]>0 ){
echo '<tr><th>' . _('RegNo') . '</th>
		<th>' . _('Name') . ':</th>';
						
		$sql = "SELECT dm.*,rs.id as calendar_id FROM registered_students rs
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		WHERE rs.subject_id= '". $_SESSION['subject'] ."'
		AND rs.period_id='".$_SESSION['semester']."'
		AND rs.class_id='".$_POST['student_class']."'";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	}		
			
		else{
		prnMsg( _('There are no Students to display for the chosen criteria'),'error');
exit();		
	}
}		
else{
	prnMsg( _('Please choose Subject and the period'),'error');
	exit();
}	
echo '<tr><td><input type="button" name="Check_All" value="Check All" onClick="Check(document.myform.tick)"></td></tr>';		
while ($row = DB_fetch_array($result))
{
	if (($j%2)==1)
	echo "<tr bgcolor=\"F0F0F0\">";
	else
	echo "<tr bgcolor=\"FFFFFF\">";
	echo "<tr><td class=\"visible\"><Input type = 'Checkbox' name ='add_id[]' id='tick' value='".$row['calendar_id']."'>"
	.$row['debtorno']."</td>";
	echo "<td class=\"visible\">".$row['name']."</td>";
	echo "</tr>";
	$j++;
}
echo "<tr><td><br><div class='centre'><input  type='Submit' name='remove' value='" . _('Deregister') . "'></div></td></tr>";
}
if (isset($_POST['remove'])){
$i=0;
if(isset($_POST['add_id'])){
foreach($_POST['add_id'] as $value){
$sql = "SELECT sm.marks,dm.name FROM studentsmarks sm
INNER JOIN debtorsmaster dm ON dm.id=sm.student_id
		WHERE calendar_id='". $_POST['add_id'][$i] ."'
		GROUP BY sm.student_id";
		$result=DB_query($sql,$db);
		$count=DB_fetch_row($result);
		$marks=$count[0];
		$study=$count[1];
if($count>0 && $marks>0){
prnMsg(_($study._(' ').'Cannot be deregistered since there are marks under this subject,please edit the marks to zero before deregistration'),'warn');
$i++;		
}
else{
$sql="DELETE FROM registered_students WHERE id='". $_POST['add_id'][$i] ."'";
	$result = DB_query($sql,$db);
	
$i++;
prnMsg(_('subjects deregistration successful'),'success');		
}//end of else

}//end of foreach

}//end of if $_POST['add_id']
include('includes/footer.inc');
			exit;
}//end of $_POST['register']	
include('includes/footer.inc');
?>
<SCRIPT LANGUAGE="JavaScript">
<!--

<!-- Begin
function Check(chk)
{
if(document.myform.Check_All.value=="Check All"){
for (i = 0; i < chk.length; i++)
chk[i].checked = true ;
document.myform.Check_All.value="UnCheck All";
}else{

for (i = 0; i < chk.length; i++)
chk[i].checked = false ;
document.myform.Check_All.value="Check All";
}
}

// End -->
</script>