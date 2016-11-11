<?php
ob_start();
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Manage Students');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo '<p class="page_title_text">' . ' ' . _('Register Students to subject') . '';
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

echo "<form method='post' name='myform'action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="enclosed"><tr><td valign=top><table class="enclosed">';

echo "<tr><td class=\"visible\">" . _('Selected') . ":</td>
<td>".$_SESSION['class_name'].' - '.$_SESSION['subject_name'].' - '.$_SESSION['term_name'].' '.$_SESSION['year_name']."
<input tabindex=21 type=submit name='clear_session' VALUE='" . _('Change Selection') . "'></td>
</tr>";
echo '<tr><td class="visible">' . _('Teacher') . ":</td>
<td class=\"visible\"><select name='teacher'>";
echo '<OPTION SELECTED VALUE="select">' . _('Select Teacher');
$sql="SELECT userid,realname FROM www_users ";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) 
{
	if ($myrow['userid']==$_POST['teacher']) 
	{
		echo '<option selected VALUE=';
	} 
	else 
	{
		echo '<option VALUE=';
	}
	echo $myrow['userid'].  '>'.' '.$myrow['realname'];
} //end while loop
echo'</table></td></tr></table>';
echo "<br><div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Load Class Students') . "'></div>";
}
}
if(!isset($_SESSION['session_object']) || isset($_POST['clear_session'])  )
{
		echo "<form method='post'  action=" . $_SERVER['PHP_SELF'] . '>';
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

if (isset($_POST['submit'])) {
	echo "<form method='post' name='myform'action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if($_POST['teacher']=="select")
	{
	echo '</br>';
	prnMsg( _('Please select teacher '.$_POST['teacher']),'error');
	}
	else{
	$_SESSION['teacher'] = $_POST['teacher'];	
	$sql = "SELECT grade_level_id FROM classes
	WHERE id='".$_SESSION['class_session']."'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$query_data = DB_fetch_row($result);
	$_SESSION['yos'] = $query_data[0];
	if (isset($_GET['pageno'])) {
	$pageno = $_GET['pageno'];
	} 
	else {
	$pageno = 1;
	}
	
	$sql = "SELECT count(*) FROM debtorsmaster";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$query_data = DB_fetch_row($result);
	$numrows = $query_data[0];
				
	$targetpage = "RegisterStudents.php";
	$rows_per_page = 25;
	$lastpage      = ceil($numrows/$rows_per_page);
	$pageno = (int)$pageno;
	if ($pageno > $lastpage) {
	   $pageno = $lastpage;
	} // if
	$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
	$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';
	
	echo '<table class="enclosed">';	
	if (isset($_SESSION['class_session']) && $_SESSION['class_session'] !=0) {
	$sql = "SELECT class_name FROM classes
	WHERE id =  '". $_POST['student_class']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);	
	
	echo '<tr><th>' . _('Add Student') . '</th>
	<th>' . _('RegNo') . ':</th>';
	$sql = "SELECT COUNT(*) FROM debtorsmaster
	WHERE  class_id= '". $_SESSION['class_session'] ."'
	AND status=0";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 )
	{		
		$sql = "SELECT * FROM debtorsmaster
		WHERE  class_id= '". $_SESSION['class_session'] ."' AND status=0
		ORDER BY name";
		$DbgMsg = _('The SQL that was used to retrieve the information was');
		$ErrMsg = _('Could not check whether the group is recursive because');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	}
	else
	{
		prnMsg( _('There are no students assigned to this class'),'error');
		exit();
	}		
	}		
	else
	{
		prnMsg( _('Please choose the search criteria'),'error');
		exit();
	}			
	echo '<tr><td>
	<input type="button" name="Check_All" value="Check All"
	onClick="Check(document.myform.tick)">
	 </td></tr>';	
	while ($row = DB_fetch_array($result))
	{
	 if (($j%2)==1)
	   echo "<tr bgcolor=\"F0F0F0\">";
	  else
	 echo "<tr bgcolor=\"FFFFFF\">";
	echo "<tr>";		
	echo "<td class=\"visible\"><Input type = 'Checkbox' name ='add_id[]' id='tick' value='".$row['id']."'>".$row['name']."</td>";
	echo "<td class=\"visible\">".$row['debtorno']."</td>";
	echo "</tr>";
	$j++;
	}
	echo "<td><br><div class='centre'><input  type='Submit' name='register' value='" . _('Register') . "'></div></td></tr>";
	}
}
if(isset($_POST['register']))
{
			
	$i=0;
	if(isset($_POST['add_id']))
	{
		foreach($_POST['add_id'] as $value)
		{
			$sql = "SELECT rs.id,cl.class_name,sub.subject_name,dm.name FROM registered_students rs 
			INNER JOIN subjects sub ON sub.id=rs.subject_id
			INNER JOIN classes cl ON cl.id=rs.class_id
			INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
			WHERE rs.student_id='". $_POST['add_id'][$i] ."'
			AND rs.subject_id='". $_SESSION['subject'] ."'
			AND rs.period_id =  '". $_SESSION['period'] ."'";
			$result=DB_query($sql,$db);
			$num=DB_num_rows($result);
			if($num>0){
			$row=DB_fetch_row($result);
			prnMsg(_($row[3]._(' ').'has already been registered for '.$row[2].' '.'under'.' '.$row[1]),'warn');
			$i++;		
			}
			else
			{
			$sql = "INSERT INTO registered_students (student_id,subject_id,period_id,class_id,teacher) 
			VALUES ('" .$_POST['add_id'][$i] ."','" .$_SESSION['subject'] ."','" .$_SESSION['period'] ."','" .$_SESSION['class_session'] ."','" .$_SESSION['teacher'] ."') ";
			$ErrMsg = _('The student could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			$i++;
			prnMsg( _('student registration successful'),'success');		
			}
		}
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