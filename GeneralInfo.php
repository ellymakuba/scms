
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
 <link rel="stylesheet" type="text/css" href="ajaxtabs/ajaxtabs.css" />
<link rel="stylesheet" type="text/css" href="css/jqueryslidemenu.css" />

<script language="JavaScript" src="js/calendar1.js"></script><!--  -->
<script type="text/javascript" src="js/formval.js"></script>

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jqueryslidemenu.js"></script>

<script type="text/javascript" src="ajaxtabs/ajaxtabs.js">

</script>


<script language=Javascript>
  function showmore(studentid)
	 {
        var url = "uploadpic.php?empid="+studentid;

        newwin = window.open(url,'Add','width=300,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }

   function attachdocs(studentid)
	 {
        var url = "attachdocs.php?empid="+studentid;

        newwin = window.open(url,'Add','width=400,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
   function Checkvalue(val,ctrl)
   {
     if (val >0 && ctrl=='numhrs')
       document.employeefrm.hpayrate.value=0;
	 if (val >0 && ctrl=='hpayrate')
       document.employeefrm.numhrs.value=0;
   }
   function Checkrent(val)
   {
     if (val==0)
       document.employeefrm.rentpayemp.value=0;

   }
   function Checkexpdate(val)
   {
     if (val!=1){
	   document.employeefrm.expdate.value="00-00-0000";
	   document.employeefrm.effdate.value="00-00-0000";
	 }
   }

   function Checkquitdate(val)
   {
     if (val==1)
	   document.employeefrm.qdate.value="00-00-0000";
	 else
	  document.employeefrm.qdate.value="";
   }

   function Checkacteffdate(val)
   {
     if (val!=1)
	   document.employeefrm.actdate.value="00-00-0000";
	 else
	  document.employeefrm.actdate.value="";
   }
</script>
<style type="text/css">
<!--

#tablet{
	border:1px solid gray;
}


-->
 </style>

</head>

<body>
<?php 

 echo "<script language=\"javascript\">
  function uploadpic(studentid)
	 {
        var url = \"uploadpic.php?studentid=\"+studentid;

        newwin = window.open(url,'View','width=300,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";
 $PageSecurity = 3;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
  
  if (!empty($_GET["studentid"]))
    $id=$_GET["studentid"];
	
  if (!empty($_GET["action"]))
    $action="update_go";
  
  if (!empty($_REQUEST["studentid"]))
  {
    $id=$_REQUEST["studentid"];
	$action = "update_go";
	
	
	$sql = "SELECT * FROM debtorsmaster
			WHERE id = '$id'";
	$ErrMsg = _('The student details could not be retrieved because');
	$result = DB_query($sql,$db,$ErrMsg);
	
	$myrow = DB_fetch_array($result);
	 $studentRegNo = $myrow['debtorno'];
	 $studentName=$myrow['name'];
	      
		$rowid = $myrow['id']; 
	     
	     if (stristr($myrow['gender'],'Male') || stristr($myrow['gender'],'M'))
		   $gender='m';
		   
		if (stristr($myrow['gender'],'Female') || stristr($myrow['gender'],'F'))
		   $gender='f';;
		     
	   // $gender=$row->Sex;
	    $age=$myrow['age']; 
	    $gradeLevel=$myrow['grade_level_id']; 
	    $course=$myrow['course_id']; 
	   	$class=$myrow['class_id'];
		
		$status=$myrow['status'];
		$studentCurrency=$myrow['currcode'];
		
	  
	
  }
  else
    $action="add";
 ?>
<form action="Students.php" method="post" name="employeefrm">
 <?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

?>
<div id="tablet"><table width="100%">
<tr><td class="visible"><strong>Student RegNo </strong></td>
   <td colspan="2" class="visible"><input name="studentNo" type="text" id="studentNo" <?php if (!empty($studentRegNo)){?> value="<?php echo $studentRegNo ?>" readonly="" <?php } ?>></td>
 <td class="visible"><strong>Student Name </strong></div></td>
   <td colspan="2" class="visible"><input name="studentName" type="text" id="studentName" <?php if (!empty($studentName)) ?> value="<?php echo $studentName ?>">
   </td>
  <td colspan="3" rowspan="6" >
		    <div style="width: 180px;">
            <hr noshade size="3" color="#eb8137">
			<?php
			if (!empty($id))  
			  echo "<center><img src='viewimg.php?imgid=$id' width=180 Height=230 ></center>"; ?>
			<hr noshade size="3" color="#eb8137">
            
      </div>
		  </td> 
   </tr>
   <tr><td class="visible"><strong>Age </strong></div></td>
   <td colspan="2" class="visible"><input name="age" type="text" id="age" <?php if (!empty($age))  echo "value=$age"; ?>></td><td class="visible"><strong>Gender </strong></div></td>
   <td colspan="2" class="visible"><input name="gender" type="text" id="gender" <?php if (!empty($gender))  echo "value=$gender"; ?>></td></tr> 
<?php				
echo '<tr><td class="visible">' . _('Course') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='course'>";
$sql2="SELECT course_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_course_id=$myrow2['course_id'];
		
if(!isset($id) || $current_course_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Course');
		$sql="SELECT id,course_name FROM courses ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['course_name'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{	
$sql2="SELECT cs.id,cs.course_name 
FROM debtorsmaster dm
INNER JOIN courses cs ON cs.id=dm.course_id
WHERE dm.id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_course=$myrow2['grade_level'];
$selected_course_id=$myrow2['id'];


$sql3="SELECT id,course_name FROM courses
ORDER BY course_name";
$result3=DB_query($sql3,$db);
while(list($coursid, $selected_course) = DB_fetch_row($result3))
                {
        if ($coursid==$selected_course_id)
         {
          echo '<option selected value="' . $coursid . '">' . $selected_course . '</option>';
          }
		
         else
       {
        echo '<option value="' . $coursid . '">' . $selected_course. '</option>';
    }
   }
DB_data_seek($result3,0);
		echo '</select></td>';
}		
$sql2="SELECT gl.id,gl.grade_level 
FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
WHERE dm.id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_grade=$myrow2['grade_level'];
$selected_id=$myrow2['id'];

echo '<td class="visible">' . _('Grade Level') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='grade_level'>";
		
$sql2="SELECT grade_level_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_grade_id=$myrow2['grade_level_id'];
		
if(!isset($id) || $current_grade_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Grade Level');
		$sql="SELECT id,grade_level FROM gradelevels";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['grade_level'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{		
		$sql="SELECT id,grade_level FROM gradelevels ";
		$result=DB_query($sql,$db);
		while(list($gradeid, $grade_level) = DB_fetch_row($result)){
		if ($gradeid==$selected_id)
         {
          echo '<option selected value="' . $gradeid . '">' . $grade_level . '</option>';
          }
         else
       {
	   
        echo '<option value="' . $gradeid . '">' . $grade_level . '</option>';
    }
   }
DB_data_seek($result,0);
		echo '</select></td></tr>';
	}	
echo '<tr><td class="visible">' . _('Class') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='student_class'>";
		
$sql2="SELECT class_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_class_id=$myrow2['class_id'];
		
if(!isset($id) || $current_class_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT id,class_name FROM classes";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{
		
$sql2="SELECT cl.id,cl.class_name 
FROM debtorsmaster dm
INNER JOIN classes cl ON cl.id=dm.class_id
WHERE dm.id='$id'
ORDER BY class_name";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_class=$myrow2['grade_level'];
$selected_class_id=$myrow2['id'];

$sql3="SELECT id,class_name FROM classes";
$result3=DB_query($sql3,$db);
while(list($classid, $selected_class) = DB_fetch_row($result3))
                {
        if ($classid==$selected_class_id)
         {
          echo '<option selected value="' . $classid . '">' . $selected_class . '</option>';
          }
         else
       {
        echo '<option value="' . $classid . '">' . $selected_class. '</option>';
    }
   }
DB_data_seek($result3,0);
		echo '</select></td>';
	}	
echo '<TD class="visible">' . _('Status') . ":</TD><TD class=\"visible\"><SELECT name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('Open');
	echo '<OPTION VALUE=1>' . _('Blocked');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Blocked');
	echo '<OPTION VALUE=0>' . _('Open');
}	

?></tr>
<tr><td ><?php echo " <a href=\"javascript:uploadpic($id)\">uploadpic</a>"; ?></td>
<td><input type="reset" name="Reset" value="Reset"></td>
<td colspan="1"><input type="submit" name="Submit" value="Submit" onClick=""></td>
<input name="action" type="hidden" <?php if (!empty($action)) echo "value=$action" ?>>
<input name="studentid" type="hidden" <?php if (!empty($id)) echo "value=$id" ?>>
<input name="tabfrom" type="hidden" <?php echo "value=GEN" ?>>
  </tr>
</table>
</div>
</form>

</body>


</html>
