<?php
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Enter Marks For Registered Students');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Enter Marks For Registered Students') . '';
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
<td><select name='exam_mode_id'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select exam mode');
$sql="SELECT id,title FROM markingperiods ";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr>';
echo "<tr><td>" . _('Out Of') . ":</td>
<td><input type='text' name='out_of' size=20></td>";
echo'</table></td></tr></table>';
echo "<br><div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Load Registered Students') . "'></div>";
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
	if($_POST['exam_mode_id']==0 || $_POST['out_of']=="")
	{
		prnMsg(_(' You must select the exam mode and fill out of field before loading registered students'),'warn');
	}
	else
	{
	$_SESSION['exam_mode'] = $_POST['exam_mode_id'];
	$_SESSION['out_of'] = $_POST['out_of'];				
	$sql = "SELECT COUNT(*) FROM registered_students
	WHERE subject_id =  '". $_SESSION['subject'] ."'
	AND period_id =  '". $_SESSION['period'] ."'
	AND class_id='". $_SESSION['class_session'] ."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if($myrow[0]==0)
	{
		echo "</br>";
		prnMsg(_('There are no students registered for this subject'),'warn');
	}
	else{
	echo '<br><table class="enclosed">';
		echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		$sql = "SELECT subject_name FROM subjects
		WHERE id =  '". $_SESSION['subject']."'"; 
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);	
		echo "<tr><td>" . _('Subject') . ":</td>
		<td>".$myrow[0]."</td>";
		$sql = "SELECT cl.class_name,gl.lower FROM classes cl
		INNER JOIN gradelevels gl ON  gl.id=cl.grade_level_id
		WHERE cl.id =  '". $_SESSION['class_session']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);	
		echo "<tr><td>" . _('Class') . ":</td>
		<td>".$myrow[0]."</td></tr>";
		$_SESSION['lower'] = $myrow[1];
			
		$sql="SELECT title FROM markingperiods where id='".$_SESSION['exam_mode']."'";
		$result=DB_query($sql,$db);
		$row=DB_fetch_row($result);
		$exam_mode=$row[0];		
	
		echo '<tr><td>' . _('Exam Mode') . ":</td>
		<td>".$exam_mode."</td></tr>";
		echo "<tr><td>" . _('Out Of') . ":</td>
		<td>".$_SESSION['out_of']."</td></tr>";
		echo '<tr><th>' . _('AdmNo') . '</th>
		<th>' . _('Name') . ':</th>
		<th>' . _('Marks') . ':</th>';
		
	$sql = "SELECT rs.*,dm.name,dm.debtorno FROM registered_students rs
	INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
	WHERE subject_id =  '". $_SESSION['subject'] ."'
	AND period_id =  '". $_SESSION['period'] ."'
	AND rs.class_id='".$_SESSION['class_session']."'
	ORDER BY dm.name ASC";
	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group is recursive because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);	
	while ($row = DB_fetch_array($result))
	{
	 if (($j%2)==1)
	echo "<tr bgcolor=\"F0F0F0\">";
	 else
	echo "<tr bgcolor=\"FFFFFF\">";
	echo "<input type='hidden' name='calendar_id[]' value='".$row['id']."'>";
	echo "<input type='hidden' name='student_id[]' value='".$row['student_id']."' >";
	echo "<tr><td class=\"visible\">".$row['debtorno']."</td>";
	echo "<td class=\"visible\">".$row['name']."</td>";
	echo "<td class=\"visible\"><input type='text' name='marks[]' size=20 ></td>";
	echo "</tr>";
	 $j++;
	}
	}
	echo "<tr><td></td><td></td><td><input type=submit name='add_marks' value='"._('insert Marks')."'></td></tr>";
	}
}
elseif (isset($_POST['add_marks']))
{
if($_SESSION['exam_mode']==0)
{
 prnMsg(_(' You must select the exam mode'),'warn');
}
else
{	
if(empty($_SESSION['out_of']))
{
 prnMsg(_(' Please fill the out of field'),'warn');
}
else
{
$i=0;
foreach($_POST['marks'] as $value)
{
if(is_numeric($_POST['marks'][$i]))
{
$sql = "SELECT name FROM debtorsmaster 
WHERE id='". $_POST['student_id'][$i] ."'";
$result=DB_query($sql,$db);
$row=DB_fetch_row($result);
$study1=$row[0];
$sql = "SELECT sm.id, dm.name FROM studentsmarks sm
INNER JOIN debtorsmaster dm ON dm.id=sm.student_id 
WHERE sm.student_id='". $_POST['student_id'][$i] ."'
AND sm.exam_mode='". $_SESSION['exam_mode'] ."'
AND sm.calendar_id =  '".$_POST['calendar_id'][$i] ."'";
$result=DB_query($sql,$db);
$count=DB_fetch_row($result);
$marks=$count[0];
$study=$count[1];
if($count>0)
{
prnMsg(_($study._(' ').'s marks has already been entered for this subject'),'warn');
}
else
{
$sql = "SELECT exam_type_id FROM markingperiods
WHERE id='". $_SESSION['exam_mode'] ."'";
$result=DB_query($sql,$db);
$row = DB_fetch_array($result);
if($row['exam_type_id']==1)
{
if($_POST['marks'][$i] > $_SESSION['out_of'])
{
prnMsg(_($study1._(' ').'Marks has exceeded out of field'),'warn'); 
}	//end of if marks>out of	
else
{
$sql = "INSERT INTO studentsmarks 
(student_id,subject_id,period_id,marks,out_of,exam_mode,calendar_id,class_id,actual_marks) 
VALUES ('" .$_POST['student_id'][$i] ."','".$_SESSION['subject']."','" .$_SESSION['period']."',
'" .($_POST['marks'][$i]/$_SESSION['out_of'])*30 ."','" .$_SESSION['out_of']."',
'" .$_SESSION['exam_mode']."','" .
$_POST['calendar_id'][$i]."','".$_SESSION['class_session']."',
'".($_POST['marks'][$i]/$_SESSION['out_of'])*100 ."') ";
$ErrMsg = _('This marks could not be added because');
$result = DB_query($sql,$db,$ErrMsg);
}
}//end of if exam type id==1
else
{
if($_POST['marks'][$i] > $_SESSION['out_of'])
{
prnMsg(_($study1._(' ').'Marks has exceeded out of field'),'warn'); 
}
else
{	
$sql = "INSERT INTO studentsmarks 
(student_id,subject_id,period_id,marks,out_of,exam_mode,calendar_id,class_id,actual_marks) 
VALUES ('" .$_POST['student_id'][$i] ."','".$_SESSION['subject']."','" .$_SESSION['period']."',
'" .($_POST['marks'][$i]/$_SESSION['out_of'])*70 ."',
'" .$_SESSION['out_of']."','" .$_SESSION['exam_mode']."',
'" .$_POST['calendar_id'][$i]."','".$_SESSION['class_session']."',
'" .($_POST['marks'][$i]/$_SESSION['out_of'])*100 ."') ";
$ErrMsg = _('This marks could not be added because');
$result = DB_query($sql,$db,$ErrMsg);
}
}//end of else exam type !=1
}//end of if marks >0
}
$i++;
}// end of foreach
prnMsg( _('Marks Added'),'success');
}//else out of field not empty
}// else exam mode selected
echo '</form>';			
}	// end of isset
include('includes/footer.inc');
?>


