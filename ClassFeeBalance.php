<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Update Fee Balance Form');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<form name='myform' method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1" width="40%">';

echo '<tr><td>' . _('Stream') . ': </td><td><select tabindex="5" name="student_class">';
$result = DB_query('SELECT * FROM classes',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['class_name']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['class_name'];
} //end while loop
	echo '</select></td></tr></table>';
		
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Display Students') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div><br>";	

if (isset($_POST['submit'])) {
$_SESSION['class'] = $_POST['student_class'];
$sql = "SELECT grade_level_id FROM classes
WHERE id='".$_SESSION['class']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$_SESSION['yos'] = $query_data[0];
	if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
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

echo '<table class=enclosed width="60%">';
	
if (isset($_POST['student_class']) && $_POST['student_class'] !=0) {
$sql = "SELECT class_name FROM classes
		WHERE id =  '". $_POST['student_class']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);	
echo "<tr><td class=\"visible\">" . _('Class') . ":</td>
	<td>".$myrow[0]."</td></tr>";

	
	
echo '<tr><th>' . _('Student Name') . '</th>
		<th>' . _('RegNo') . ':</th>
		<th>' . _('Fee Balance') . ':</th>';
		
		
$sql = "SELECT COUNT(*) FROM debtorsmaster
		WHERE  class_id= '". $_POST['student_class'] ."'
		AND status=0";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]>0 ){		
		$sql = "SELECT * FROM debtorsmaster
		WHERE  class_id= '". $_POST['student_class'] ."'
		AND status=0
		ORDER BY name";
        $DbgMsg = _('The SQL that was used to retrieve the information was');
        $ErrMsg = _('Could not check whether the group is recursive because');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		}
		else{
		prnMsg( _('There are no records to display Currently'),'error');
exit();
}		


}		
else{
prnMsg( _('Please choose a class'),'error');
exit();
}			
	
			while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
		echo "<tr>";		
		echo "<input type='hidden' name='add_id[]' value='".$row['id']."'>";
		echo "<td class=\"visible\">".$row['name']."</td>";
		echo "<td class=\"visible\">".$row['debtorno']."</td>";
		echo "<td class=\"visible\"><input type='text' name='balance[]' size=20 ></td>";
		    echo "</tr>";
		  $j++;
			}
			

echo "<td><br><div class='centre'><input  type='Submit' name='register' value='" . _('Update') . "'></div></td></tr>";
}
if (isset($_POST['register'])){	
		
$i=0;
foreach($_POST['balance'] as $value){
	if($_POST['balance'][$i]>0)
	{
		$sql = "UPDATE debtorsmaster SET balance='" .$_POST['balance'][$i] ."' WHERE id='" .$_POST['add_id'][$i] ."'";
				$ErrMsg = _('The student could not be updated because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg( _('Update successful'),'success');		
	
	}
$i++;	
}
include('includes/footer.inc');
			exit;
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