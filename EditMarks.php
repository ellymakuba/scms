
<?php
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Edit Marks');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Edit Entered Marks') . '';
if(( isset($_POST['view']) || isset($_SESSION['session_object'])) && !isset($_POST['clear_session']))
{
if(($_POST['student_class']==0 || $_POST['subject']==0 || $_POST['period']==0)&& !isset($_SESSION['session_object']))
{
	echo '</br>';
	prnMsg( _('Please select all the fields before setting session'),'error');
}
else{
if(!isset($_SESSION['session_object']))
{
	$_SESSION['class_session']=$_POST['student_class'];
	$_SESSION['subject'] = $_POST['subject'];
	$_SESSION['period'] = $_POST['period'];
	$_SESSION['session_object']=$_SESSION['class_session'].' '.$_SESSION['subject'].' '.$_SESSION['period'];	
}
$sql = "SELECT class_name FROM classes 
WHERE id='".$_SESSION['class_session']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$_SESSION['class_name'] = $query_data[0];

$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
INNER JOIN terms ON terms.id=cp.term_id
INNER JOIN years ON years.id=cp.year 
WHERE cp.id='".$_SESSION['period']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$_SESSION['term_name'] = $query_data[1];
$_SESSION['year_name'] = $query_data[2];

$sql = "SELECT subject_name FROM subjects 
WHERE id='".$_SESSION['subject']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$_SESSION['subject_name'] = $query_data[0];

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="enclosed"><tr><td valign=top><table class="enclosed">';

echo "<tr><td class=\"visible\">" . _('Selected') . ":</td>
<td>".$_SESSION['class_name'].' - '.$_SESSION['subject_name'].' - '.$_SESSION['term_name'].' '.$_SESSION['year_name']."
<input tabindex=21 type=submit name='clear_session' VALUE='" . _('Change Selection') . "'></td>
</tr>";
	
echo '<tr><td>' . _('Exam Mode') . ":</td>
<td><select name='exam_mode'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select exam mode');
$sql="SELECT id,title FROM markingperiods ";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr>';
echo'</table></td></tr></table>';
echo "<br><div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Load Student Marks') . "'></div>";
echo '</form>';
}
}
if(!isset($_SESSION['session_object']) || isset($_POST['clear_session'])  )
{
		echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class=enclosed><tr><td valign=top><table  class=enclosed>';
		echo '<tr><td>' . _('Stream') . ': </td><td><select tabindex="5" name="student_class">';
		$result = DB_query('SELECT * FROM classes',$db);
		while ($myrow = DB_fetch_array($result)) 
		{
			if ($myrow['id']==$_POST['student_class']) 
			{
				echo '<option selected VALUE=';
			} 
			else 
			{
				echo '<option VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Period') . ":</td>
		<td><select name='period'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Period');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) 
		{
			//echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
			if ($myrow['id']==$_POST['period']) 
			{
				echo '<option selected VALUE=';
			} 
			else 
			{
				echo '<option VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['title'].' '.$myrow['year'];
		} //end while loop
		
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Subject') . ":</td>
		<td><select name='subject'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Subject');
		$sql="SELECT id,subject_name FROM subjects 
		ORDER BY subject_name";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) 
		{
			
			if ($myrow['id']==$_POST['subject']) 
			{
				echo '<option selected VALUE=';
			} 
			else 
			{
				echo '<option VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['subject_name'];
		} //end while loop
		echo '</select></td></tr>';
		echo'</table></td></tr></table>';
		echo "<br><div class='centre'><input tabindex=20 type='Submit' name='view' value='" . _('Set Class Session') . "'></div>";	
		echo '</form>';	
}	
if(isset($_POST['clear_session']))
{
	unset($_SESSION['session_object']);
}

if (isset($_POST['submit']))
 {
	if($_POST['exam_mode']==0)
	{
	echo '</br>';
	prnMsg( _('Please select Exam Mode'),'error');
	}
	else{
	$_SESSION['exam_mode'] = $_POST['exam_mode'];
	echo '<br><table class="enclosed">';
	echo "<form name='myform' method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$sql = "SELECT fullaccess FROM www_users
	WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$user=$myrow[0];
	$todays_date=FormatDateForSQL($todays_date);
	
	$sql="SELECT year_status,year FROM collegeperiods
	WHERE id= '" . $_SESSION['period'] . "'";
	$result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	$year_status=$myrow['year_status'];
	$year_period=$myrow['year'];
	if($year_status==1){
	prnMsg(_('This periods academic year has been closed'),'warn');
	exit("Please note that you cannot add/edit marks of a clossed period");
	}
	$sql2 = "SELECT run FROM years
	WHERE id='".$year_period."'";
	$result2 = DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$run=$myrow2['run'];
	if($run==1)
	{
	prnMsg(_('This academic year has already been compiled and cannot be edited'),'warn'); 
	exit("Note that editing was disabled due to upholding data intergrity");
	}
	
	$sql = "SELECT out_of FROM studentsmarks
	WHERE exam_mode='". $_SESSION['exam_mode'] ."'
	AND class_id='" .$_SESSION['class_session'] ."'";
	$result=DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$_SESSION['outof']=$row['out_of'];
	echo "<tr><td colspan=2>" . _('Out Of') . ":</td>
	<td>".$_SESSION['outof']."</td></tr>";
	
	echo '<tr><th>' . _('ID') . '</th>
	<th>' . _('AdmNo') . ':</th>
	<th>' . _('Name') . ':</th>
	<th>' . _('Marks') . ':</th></tr>';
	echo '<tr><td>
	<input type="button" name="Check_All" value="Check All"
	onClick="Check(document.myform.tick)">
	</td></tr>';
	$sql = "SELECT exam_type_id FROM markingperiods
	WHERE id='". $_SESSION['exam_mode'] ."'";
	$result=DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$real_type=$row['exam_type_id'];
	$sql = "SELECT rs.id as ids,rs.subject_id,rs.student_id,sm.marks,dm.debtorno,dm.name 
	FROM registered_students rs
	INNER JOIN studentsmarks sm ON rs.id=sm.calendar_id
	INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
	WHERE rs.subject_id =  '". $_SESSION['subject'] ."'
	AND rs.period_id =  '". $_SESSION['period'] ."'
	AND sm.exam_mode='". $_SESSION['exam_mode'] ."'
	AND rs.class_id='". $_SESSION['class_session'] ."'
	ORDER BY dm.name";
	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group is recursive because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$count=DB_num_rows($result);
	if($count==0){
	echo '<tr><td>';
	 prnMsg(_('There are no records to display'),'warn');
	 echo '</td></tr>';
	}
	else
	{	
	while ($row = DB_fetch_array($result))
	{
		echo "<tr>";
		echo "<td class=\"visible\"><input type='checkbox' name='calendar_id[]'  id='tick'
		value='".$row['ids']."'>".$row['ids']."</td>"; 
		echo "<td class=\"visible\"><input type='text' name='student_id2[]' value='".$row['debtorno']."' readonly=''></td>"; 
		echo "<td width='50%' class=\"visible\" >".$row['name']."</td>"; 
		echo "<td class=\"visible\">"; ?><input type="text" name='marks<?php echo $row['ids']; ?>' id='marks' value='<?php echo $row['marks']; ?>'  > <?php "</td>";
		echo "</tr>";
	}
	echo "<tr><td></td><td></td><td><input type=submit name='edit_marks' value='"._('Edit Marks')."'></td><td><input  type=submit name='delete_marks' VALUE='" . _('Delete') . "'></td></tr>";
	}
	echo '</table><br></form>';
	}		
}
if (isset($_POST['edit_marks'])){
$i=0;

foreach($_POST['calendar_id'] as $id){
$sql = "SELECT exam_type_id FROM markingperiods
		WHERE id='". $_SESSION['exam_mode'] ."'";
		$result=DB_query($sql,$db);
		$row = DB_fetch_array($result);
	if($row['exam_type_id']==1)
	{
		if($_POST['marks'.$id] > $_SESSION['outof'])
		{
		   prnMsg(_('Marks cannot exceed Out of field'),'warn'); 
		   exit();
		}
		$sql = "UPDATE studentsmarks  SET marks='" .$_POST['marks'.$id]/$_SESSION['outof']*30 ."',
		actual_marks='" .$_POST['marks'.$id]/$_SESSION['outof']*100 ."'
		WHERE calendar_id='" .$id ."'
		AND exam_mode='" .$_SESSION['exam_mode'] ."'";
		$ErrMsg = _('This marks could not be updated because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg( _('Marks updated'),'success');
   }
	else
	{
			if($_POST['marks'.$id] > $_SESSION['outof'])
			{
				 prnMsg(_('Exam marks cannot exceed out of field'),'warn'); 
				 exit();
			}	
			$sql = "UPDATE studentsmarks  SET marks='" .$_POST['marks'.$id]/$_SESSION['outof'] *70 ."',
			actual_marks='" .$_POST['marks'.$id]/$_SESSION['outof']*100 ."'
			WHERE calendar_id='" .$id."'
			AND exam_mode='" .$_SESSION['exam_mode'] ."'";
			$ErrMsg = _('This marks could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Marks updated'),'success');
	}//end of else
}
$i++;
}

if (isset($_POST['delete_marks'])){
	foreach($_POST['calendar_id'] as $id){
	$sql = "DELETE FROM studentsmarks 
		WHERE calendar_id='" .$id ."'
		AND exam_mode='" .$_SESSION['exam_mode'] ."'";
		$ErrMsg = _('This marks could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Marks deleted'),'success');
	
	}
}		

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
