<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Manage Autobilling');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('Class') . ":</td>
		<td><select name='class_id'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT cl.id,cl.class_name,c.course_name,gl.grade_level FROM classes cl 
		INNER JOIN courses c ON c.id=cl.course_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		ORDER BY class_name";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'] . '>' . $myrow['class_name']._('-').$myrow['grade_level']._('-').$myrow['course_name'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr>';
echo '<tr><td>' . _('Period') . ":</td>
		<td><select name='period'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Period');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr></table>';
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='register' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";		
		
if (isset($_POST['register'])) {
$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period'];
	
echo '<table border="1">';


$sql = "SELECT cl.class_name,c.course_name,gl.grade_level FROM classes cl
	INNER JOIN courses c ON c.id=cl.course_id
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
	WHERE cl.id = '".$_SESSION['class']."'";
     $result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	$course_name=$myrow['course_name'];	
	$grade_level=$myrow['grade_level'];
	echo "<tr><td>" . _('Class') . ":</td>
	<td>".$myrow['course_name']."</td>";
	
$sql="SELECT t.title FROM terms t
INNER JOIN collegeperiods cp ON cp.term_id=t.id
WHERE cp.id='".$_SESSION['period']."'";
$result=DB_query($sql,$db);
$myrow = DB_fetch_array($result);
echo "<tr><td>" . _('Period') . ":</td>
	<td>".$myrow['title']."</td>";
	
echo "<tr><td>" . _('Grade Level') . ":</td>
	<td>".$grade_level."</td>";	
	
echo '<tr><th>' . _('Receipt Product') . '</th>
<th>'. _('Amount') . ':</th>
		<th>' . _('priority') . ':</th>';
	 $sql = 'SELECT id,stockid, description FROM stockmaster WHERE 
	 categoryid = "ACADMS"';
     $DbgMsg = _('The SQL that was used to retrieve the information was');
     $ErrMsg = _('Could not check whether the group is recursive because');
     $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		 
while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
		echo "<tr><td><Input type = 'checkbox' name ='id[]' value='".$row['id']."' readonly=''>".$row['description']."</td>";
			
	
echo "<td>"; ?><input type="text" name='amount<?php echo $row['id']; ?>' id='amount'  size='10' > <?php "</td>";
		  echo "<td>"; ?><input type="text" name='priority<?php echo $row['id']; ?>' id='priority'  size='3' > <?php "</td>";
		    echo "</tr>";
		  $j++;	  
			}		 		
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	
}

if (isset($_POST['submit'])) {
$sql = "SELECT id FROM autobilling
		WHERE course_id='". $_SESSION['class'] ."'
		AND term_id='". $_SESSION['period'] ."'";
		$result=DB_query($sql,$db);
		$myrow = DB_fetch_array($result);
		$edit_id=$myrow['id'];	

$sql="DELETE  FROM autobilling_items 
WHERE autobilling_id='$edit_id'";
$result = DB_query($sql,$db);


foreach($_POST['id'] as $value){
if($_POST['amount'.$value]>0){
$sql5="SELECT stockid FROM stockmaster 
WHERE id='".$value."'";
$result5=DB_query($sql5,$db);
$myrow5 = DB_fetch_array($result5);
$stock_id=$myrow5['stockid'];
$sql = "SELECT id FROM autobilling_items
		WHERE autobilling_id='". $id ."'
		AND product_id='". $value ."'";
		$result=DB_query($sql,$db);
if(DB_fetch_row($result)>0){
prnMsg(_($value._(' ').'has already been invoiced for this course'),'warn');	
}
else{

$sql = "INSERT INTO autobilling_items (autobilling_id,product_id,amount,priority) 
		VALUES ('" .$edit_id ."','" .$stock_id ."','" .$_POST['amount'.$value] ."','" .$_POST['priority'.$value]."') ";
	$result=DB_query($sql,$db);
prnMsg( _('products added successfully'),'success');
}		
}
}
}
include('includes/footer.inc');
?>
