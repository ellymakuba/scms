<?php
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Student Registration Form');
$id=$_REQUEST['id'];
$_SESSION['passport']=$_REQUEST['id'];
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text">'. ' ' . _('Student Registration Form') . '';
if (isset($Errors)) 
{
unset($Errors);
}
$Errors = array();
if(isset($_POST['removePassport']))
{
    $emplooyeePassport=$_POST['passport'];
   	$target_path = "uploads/$studentPassport";
	echo $_POST['passport'];
	unlink($target_path);
	unset($_POST['passport']);
	$null='';
	$sql = "UPDATE debtorsmaster SET studentPhoto='' WHERE id = '".$_SESSION['passport']."'";
	$ErrMsg = _('The student passport could not be edited');
	$DbgMsg = _('The SQL that was used to update the student but failed was');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	prnMsg(_('The student passport for') . ' ' . $id . ' ' . _('has been updated'),'success');
}
$_POST['CustName']= strtoupper($_POST['CustName']);
if (isset($_POST['submit'])) {
$target_path = "uploads/";
if(!empty($_FILES['studentPhoto']['tmp_name'])){
	$target_path_studentPhoto = $target_path . basename( $_FILES['studentPhoto']['name']); 
	if ((($_FILES["studentPhoto"]["type"] == "image/gif")|| ($_FILES["studentPhoto"]["type"] == "image/jpeg")
	|| ($_FILES["studentPhoto"]["type"] == "image/jpg")
	|| ($_FILES["studentPhoto"]["type"] == "image/png"))
	&& ($_FILES["studentPhoto"]["size"] < 10000))
	 {
	 if ($_FILES["studentPhoto"]["error"] > 0) 
	 {
		echo "<ul><li>".$_FILES["studentPhoto"]["error"]."</li></ul>";
		  $InputError=1;
	  } 
	  else
	   {
		if (file_exists("uploads/" . $_FILES["studentPhoto"]["name"])) 
		{
		  prnMsg( _('File already exists'),'success');
		  $InputError=1;
		} 
		else 
		{
		  move_uploaded_file($_FILES['studentPhoto']['tmp_name'], $target_path_studentPhoto);
		}
	  }//end of else
	}//end of if 
	else {
	  prnMsg( _('Photo does not meet required criteria, check file extension.(NB: size should be less than 2kb)'),'success');
		  $InputError=1;
	}
}
	$InputError = 0;
	$i=1;
	$sql="SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 and isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( _('The student registration number already exists in the database'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	}
	
	$sql="SELECT COUNT(debtorno) FROM debtorsmaster 
	WHERE debtorno='".$_POST['DebtorNo']."'
	AND id !='$id'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 and !isset($_POST['New'])) {
	$InputError = 1;
	prnMsg( _('The student registration number already exists in the database'),'error');
	$Errors[$i] = 'DebtorNo';
	$i++;
	}
	
	 elseif (strlen($_POST['CustName']) > 40 OR strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( _('The Student Name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		prnMsg( _('The admission no cannot be empty'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND ContainsIllegalCharacters($_POST['DebtorNo'])) {
		$InputError = 1;
		prnMsg( _('The Student AdmNo cannot contain any of the following characters') . " . - ' & + \" " . _('or a space'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( _('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (strlen($_POST['Address6']) >15) {
		$InputError = 1;
		prnMsg( _('The Line 6 of the address must be fifteen characters or less long'),'error');
		$Errors[$i] = 'Address6';
		$i++;
	}
	
	elseif (strlen($_POST['Fax']) >10) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Fax';
		$i++;
	}

	if ($InputError !=1){
		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
		if (!isset($_POST['New'])) {
			$sql = "SELECT count(id) FROM debtortrans
			where debtorno = '" . $id . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_array($result);

			if ($myrow[0] == 0) {
			$sqlclass = "SELECT grade_level_id FROM classes where id = '" . $_POST['student_class'] . "'";
			$resultclass = DB_query($sqlclass,$db);
			$myrowclass = DB_fetch_array($resultclass);
			$stream_class=$myrowclass['grade_level_id'];
			
			  $sql = "UPDATE debtorsmaster SET
			  		debtorno='" . $_POST['DebtorNo'] . "',
			  		name='" . $_POST['CustName'] . "',
					age='" . $_POST['age'] . "',
					dob='" . $_POST['dob'] . "',
					gender='" . $_POST['gender'] . "',
					class_id='" . $_POST['student_class'] . "',
					boxno='" . $_POST['boxno'] . "',
					town='" . $_POST['town'] . "',
					zip='" . $_POST['zip'] . "',
					state='" . $_POST['state'] . "',
					mobileno='" . $_POST['mobileno'] . "',
					relationship='" . $_POST['relationship'] . "',
					gname='" . $_POST['gname'] . "',
					gboxno='" . $_POST['gboxno'] . "',
					gtown='" . $_POST['gtown'] . "',
					gstate='" . $_POST['gstate'] . "',
					balance='" . $_POST['balance'] . "',
					gmobileno='" . $_POST['gmobileno'] . "',
					currcode='" . $_POST['CurrCode'] . "',
					email='" . $_POST['Email'] . "',
					group_id='" . $_POST['student_group'] . "',
					status='" . $_POST['Blocked'] . "',
					final_grade='" . $_POST['final_grade'] . "',
					transport='" . $_POST['transport'] . "',
					estate_id='" . $_POST['estate'] . "',
					disease='" . $_POST['disease'] . "',
					pschool='" . $_POST['pschool'] . "',
					house='" . $_POST['house'] . "',
					index_no='" . $_POST['index_no'] . "'
				  WHERE id = '" . $id . "'";
			} else {
			$sqlclass = "SELECT grade_level_id FROM classes
			where id = '" . $_POST['student_class'] . "'";
			$resultclass = DB_query($sqlclass,$db);
			$myrowclass = DB_fetch_array($resultclass);
			$stream_class=$myrowclass['grade_level_id'];
			
			  $currsql = "SELECT currcode FROM debtorsmaster
			  where id = '" . $_POST['id'] . "'";
			  $currresult = DB_query($currsql,$db);
			  $currrow = DB_fetch_array($currresult);
			  $OldCurrency = $currrow[0];

			  $sql = "UPDATE debtorsmaster SET
			  		debtorno='" . $_POST['DebtorNo'] . "',
			  		name='" . $_POST['CustName'] . "',
					age='" . $_POST['age'] . "',
					dob='" . $_POST['dob'] . "',
					gender='" . $_POST['gender'] . "',
					class_id='" . $_POST['student_class'] . "',
					boxno='" . $_POST['boxno'] . "',
					town='" . $_POST['town'] . "',
					zip='" . $_POST['zip'] . "',
					state='" . $_POST['state'] . "',
					mobileno='" . $_POST['mobileno'] . "',
					relationship='" . $_POST['relationship'] . "',
					gname='" . $_POST['gname'] . "',
					gboxno='" . $_POST['gboxno'] . "',
					gtown='" . $_POST['gtown'] . "',
					gstate='" . $_POST['gstate'] . "',
					balance='" . $_POST['balance'] . "',
					gmobileno='" . $_POST['gmobileno'] . "',
					currcode='" . $_POST['CurrCode'] . "',
					email='" . $_POST['Email'] . "',
					group_id='" . $_POST['student_group'] . "',
					status='" . $_POST['Blocked'] . "',
					final_grade='" . $_POST['final_grade'] . "',
					transport='" . $_POST['transport'] . "',
					estate_id='" . $_POST['estate'] . "',
					disease='" . $_POST['disease'] . "',
					pschool='" . $_POST['pschool'] . "',
					house='" . $_POST['house'] . "',
					index_no='" . $_POST['index_no'] . "'
				  WHERE id = '" . $id . "'";

			  if ($OldCurrency != $_POST['CurrCode']) {
			  	prnMsg( _('The currency code cannot be updated as there are already transactions for this student'),'info');
			  }
			}

			$ErrMsg = _('The student could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Student updated'),'success');
			if(isset($_FILES['studentPhoto']['name'])){
			$sql="UPDATE debtorsmaster SET studentPhoto='" . $_FILES['studentPhoto']['name'] . "',photosize='".$_FILES["studentPhoto"]["size"]."' WHERE id = '$id'";
			$result = DB_query($sql, $db);
			}
			echo '<br>';

		} 
		else {			
	$sqlclass = "SELECT grade_level_id FROM classes
	where id = '" . $_POST['student_class'] . "'";
	$resultclass = DB_query($sqlclass,$db);
	$myrowclass = DB_fetch_array($resultclass);
	$stream_class=$myrowclass['grade_level_id'];	

	$sql = "INSERT INTO debtorsmaster (debtorno,name,age,gender,boxno,town,	   	 
	zip,state,mobileno,grade_level_id,class_id,course_id,relationship,gname,gboxno,gstate,
	gmobileno,email,group_id,final_grade,transport,estate_id,dob,disease,pschool,house,index_no,balance,studentPhoto)
	VALUES ('" . $_POST['DebtorNo'] ."','" . $_POST['CustName'] ."','" . $_POST['age'] ."',
'" . $_POST['gender'] ."','" . $_POST['boxno'] . "','" . $_POST['town'] . "','" . $_POST['zip'] . "',
'" . $_POST['state'] . "','" . $_POST['mobileno'] . "','" . $stream_class . "','" . $_POST['student_class'] . "','" . $_POST['course'] . "','" . ($_POST['relationship']). "','" . $_POST['gname'] . "','" . ($_POST['gboxno']). "','" . $_POST['gstate'] . "','" . $_POST['gmobileno'] . "','" . $_POST['Email'] . "','" . $_POST['student_group'] . "','" . $_POST['final_grade'] . "','" . $_POST['transport'] . "','" . $_POST['estate'] . "','" . $_POST['dob'] . "','" . $_POST['disease'] . "','" . $_POST['pschool'] . "','" . $_POST['house'] . "','" . $_POST['index_no'] . "','" . $_POST['balance'] . "','".$_FILES['studentPhoto']['name']."')";
			$ErrMsg = _('This student could not be added because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Student Added'),'success');	
	include('includes/footer.inc');
	exit;
		}
	} else {
		prnMsg( _('Validation failed') . '. ' . _('No updates or deletes took place'),'error');
	}

} elseif (isset($_POST['delete'])) {
	$CancelDelete = 0;
	$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $id . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This student cannot be deleted because there are transactions that refer to it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this student');

	} 
	$sql= "SELECT COUNT(*) FROM registered_students WHERE student_id='" . $id . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This student cannot be deleted because there are subjects registered under him'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('subjects against this student');

	}
	$sql= "SELECT COUNT(*) FROM studentsmarks WHERE student_id='" . $id . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This student cannot be deleted because there are marks under him'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('subject marks against this student');

	}
	if ($CancelDelete==0) {
		$sql="DELETE FROM debtorsmaster WHERE id='" . $id . "'";
		$result = DB_query($sql,$db);
		prnMsg( _('Student') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted') . ' !','success');
		include('includes/footer.inc');
		unset($_SESSION['CustomerID']);
		unset($_POST['passport']);
		exit;
	} //end if Delete Customer
}

if(isset($reset)){
	unset($_POST['CustName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['Phone']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Discount']);
	unset($_POST['DiscountCode']);
	unset($_POST['PymtDiscount']);
	unset($_POST['CreditLimit']);
// Leave Sales Type set so as to faciltate fast customer setup
//	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
// Leave Type ID set so as to faciltate fast customer setup
//	unset($_POST['typeid']);
}

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['ws'])){
	$ws = $_POST['ws'];
} elseif (isset($_GET['ws'])){
	$ws = $_GET['ws'];
}
if (isset($_POST['Edit'])){
	$Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])){
	$Edit = $_GET['Edit'];
} else {
	$Edit='';
}

if (isset($_POST['Add'])){
	$Add = $_POST['Add'];
} elseif (isset($_GET['Add'])){
	$Add = $_GET['Add'];
}


if (!isset($id)) {	
	$sql='SELECT COUNT(typeid) FROM debtortype';
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($SetupErrors>0) {
		echo '<br /><div class=centre><a href="'.$_SERVER['PHP_SELF'] .'" >'._('Click here to continue').'</a></div>';
		include('includes/footer.inc');
		exit;
	}
	echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo "<input type='Hidden' name='New' value='Yes'>";
	$DataError =0;
	echo '<table class="enclosed"><tr><td class="visible"><table class="enclosed">';
	echo '<tr><td colspan="2" class="visible"><h3>General Information</h3></td></tr>';
	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<tr><td class="visible">' . _('Student AdmNo') . ":</td><td class=\"visible\"><input tabindex=1 type='Text' name='DebtorNo' size=30 maxlength=30></td></tr>";
	}

	echo '<tr><td class="visible">' . _('Student Name') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="CustName" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Index NO') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="index_no" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Age') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="age" size=30 maxlength=15></td></tr>';
	echo '<tr><td class="visible">' . _('Gender') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="gender" size=30 maxlength=20></td></tr>';
	echo '<tr><td class="visible">' . _('DOB') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="dob" size=30 maxlength=40></td></tr>';		
$sql2="SELECT gl.id,gl.grade_level 
FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
WHERE dm.debtorno='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_grade=$myrow2['grade_level'];
$selected_id=$myrow2['id'];
	
	
	
$sql2="SELECT gl.id,gl.grade_level 
FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.final_grade
WHERE dm.id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_grade=$myrow2['grade_level'];
$selected_id=$myrow2['id'];
		
echo '<tr><td class="visible">' . _('Stream') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='student_class'>";
		
$sql2="SELECT class_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_class_id=$myrow2['class_id'];
		
if(!isset($id) || $current_class_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stream');
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
	
	
echo '<tr><td class="visible">' . _('Group') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='student_group'>";		
$sql2="SELECT group_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_group_id=$myrow2['group_id'];
		
if(!isset($id) || $current_group_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Group');
		$sql="SELECT id,status FROM status";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['status'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{
$sql3="SELECT id,status FROM status";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_group_id)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
		
	}
		
		
echo '<tr><td class="visible">' . _('Stage') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='estate'>";
		
$sql2="SELECT estate_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_estate=$myrow2['estate_id'];
		
if(!isset($id) || $current_estate==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stage');
		$sql="SELECT id,estate FROM estates ORDER BY estate";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['estate'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{

$sql3="SELECT id,estate FROM estates";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_estate)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
}	
echo '<TR><td class="visible">' . _('Transport') . ":</TD><td class=\"visible\"><SELECT name='transport'>";
if ($_POST['transport']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}
'</SELECT></TD></TR>';

echo '<TR><td class="visible">' . _('Status') . ":</TD><td class=\"visible\"><SELECT name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('Present');
	echo '<OPTION VALUE=1>' . _('Transfered/Completed');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Transfered/Completed');
	echo '<OPTION VALUE=0>' . _('Present');
}
'</SELECT></TD></TR>';		 
  echo '</table></td><td class="visible"><table class="enclosed">';
echo '<tr><td colspan="2" class="visible"><h3>Student Contact & Medical</h3></td></tr>';
	
echo '<tr><td class="visible">' . _('Constituency') . ':</td>
		<td class="visible"><input  type="Text" name="boxno" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('County') . ':</td>
		<td class="visible"><input  type="Text" name="town" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('District') . ':</td>
		<td class="visible"><input tabindex=4 type="Text" name="zip" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Location') . ':</td>
		<td class="visible"><input tabindex=5 type="Text" name="state" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Sub-location') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="mobileno" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('Disease(if any)') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="disease" size=30 maxlength=40></td></tr>';
 echo '<tr><td class="visible">' . _('Previous School') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="pschool" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('House:') . '</TD><td class="visible"><SELECT Name="house">';
		$sql = 'SELECT * FROM houses';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['house']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['house'];
		} //end while loop
	echo '</SELECT></TD></TR>';		
	echo '<TR><td class="visible">Passport</br>(150px * 110px)</td><TD class="visible"><input type="file" name="studentPhoto"></TD></TR>';	
 echo '</table></td><td class="visible"><table class="enclosed">';
 echo '<tr><td colspan="2" class="visible"><h3>Guardian Contact</h3></td></tr>';

 echo '<tr><td class="visible">' . _('Relationship To Student') . ':</td>
		<td class="visible"><input tabindex=2 type="Text" name="relationship" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Full Name') . ':</td>
		<td class="visible"><input tabindex=3 type="Text" name="gname" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Mobile No') . ':</td>
		<td class="visible"><input tabindex=5 type="Text" name="gmobileno" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('P.O BOX') . ':</td>
		<td class="visible"><input tabindex=4 type="Text" name="gboxno" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Town') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="gtown" size=30 maxlength=40></td></tr>';
	echo '<tr><td class="visible">' . _('Postal Code') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="gstate" size=30 maxlength=40></td></tr>';
		echo '<tr><td class="visible">' . _('Fee Balance') . ':</td>
		<td class="visible"><input tabindex=6 type="Text" name="balance" size=30 maxlength=40></td></tr>';
	echo'</table></td></tr></table>';
	if ($DataError ==0){
		echo "<br><div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Add New Student') . "'>&nbsp;<input tabindex=21 type=submit action=RESET VALUE='" . _('Reset') . "'></div>";
	}
	echo '</form>';

} else {

	echo "<form method='post' enctype='multipart/form-data' action='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';
	
	echo '<tr><td valign=top class="visible"><table class="enclosed">';
	if (!isset($_POST['New'])) {
		$sql = "SELECT * FROM debtorsmaster WHERE debtorsmaster.id = '" . $id . "'";
		$ErrMsg = _('The student details could not be retrieved because');
		$result = DB_query($sql,$db,$ErrMsg);
		$myrow = DB_fetch_array($result);
		echo '<tr><td colspan="2" class="visible"><h3>General Information</h3></td></tr>';		
		$_POST['DebtorNo'] = $myrow['debtorno'];
		$_POST['CustName'] = $myrow['name'];
		$_POST['age']  = $myrow['age'];
		$_POST['gender']  = $myrow['gender'];
		$_POST['grade_level']  = $myrow['grade_level_id'];
		$_POST['course']  = $myrow['course_id'];
		$_POST['student_class'] = $myrow['class_id'];
		$_POST['boxno'] = $myrow['boxno'];
		$_POST['town'] = $myrow['town'];
		$_POST['zip'] = $myrow['zip'];
		$_POST['state']  = $myrow['state'];
		$_POST['mobileno']  = $myrow['mobileno'];
		$_POST['disease']  = $myrow['disease'];
		$_POST['pschool']  = $myrow['pschool'];
		$_POST['relationship']  = $myrow['relationship'];
		$_POST['gname']  = $myrow['gname'];
		$_POST['gboxno']  = $myrow['gboxno'];
		$_POST['gtown']	= $myrow['gtown'];
		$_POST['gstate'] = $myrow['gstate'];
		$_POST['balance'] = $myrow['balance'];
		$_POST['Email'] = $myrow['email'];
		$_POST['gmobileno'] = $myrow['gmobileno'];
		$_POST['disease'] = $myrow['disease'];
		$_POST['pschool'] = $myrow['pschool'];
		$_POST['dob'] = $myrow['dob'];
		$_POST['house'] = $myrow['house'];
		$_POST['index_no'] = $myrow['index_no'];
		$passport=$myrow['studentPhoto'];
		echo '<input type=hidden name="id" value="' . $id . '">';

	} else {
		echo '<input type=hidden name="New" value="Yes">';
		echo '<tr><td colspan="2" class="visible"><h3>General Information</h3></td></tr>';		
	}
	if (isset($_GET['Modify'])) {
	echo '<tr><td class="visible">' . _('Student AdmNo') . ':</td><td class="visible">' . $_POST['DebtorNo'] . '</td></tr>';
		echo '<tr><td class="visible">' . _('Student Name') . ':</td><td class="visible">' . $_POST['CustName'] . '</td></tr>';
		echo '<tr><td class="visible">' . _('Index NO') . ':</td><td class="visible">' . $_POST['index_no'] . '</td></tr>';
		echo '<tr><td class="visible">' . _('Age') . ':</td><td class="visible">' . $_POST['age'] . '</td></tr>';
		echo '<tr><td class="visible">' . _('Gender') . ':</td><td class="visible">' . $_POST['gender'] . '</td></tr>';
		echo '<tr><td class="visible">' . _('DoB') . ':</td><td class="visible">' . $_POST['dob'] . '</td></tr>';
		
$sql2="SELECT gl.id,gl.grade_level 
FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
WHERE dm.id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_grade=$myrow2['grade_level'];
$selected_id=$myrow2['id'];	
	
echo '<tr><td class="visible">' . _('Stream') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='student_class'>";
		
$sql2="SELECT class_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_class_id=$myrow2['class_id'];
		
if(!isset($id) || $current_class_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stream');
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
	
echo '<tr><td class="visible">' . _('Group') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='student_group'>";
		
$sql2="SELECT group_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_group_id=$myrow2['group_id'];
		
if(!isset($id) || $current_group_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Group');
		$sql="SELECT id,status FROM status";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['status'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{
$sql3="SELECT id,status FROM status";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_group_id)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
		echo '</select></td>';
	}

echo '<tr><td class="visible">' . _('Stage') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='estate'>";
		
$sql2="SELECT estate_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_estate=$myrow2['estate_id'];
		
if(!isset($id) || $current_estate==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stage');
		$sql="SELECT id,estate FROM estates ORDER BY estate";
				$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['estate'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{

$sql3="SELECT id,estate FROM estates";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_estate)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
	echo '</select></td>';	
	}		
		echo '</table></td><td class="visible"><table class="enclosed">';
	} else {
		echo '<tr><td class="visible">' . _('Student AdmNo') . ':</td>
			<td class="visible"><input ' . (in_array('DebtorNo',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="DebtorNo" value="' . $_POST['DebtorNo'] . '" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('Student Name') . ':</td>
			<td class="visible"><input ' . (in_array('CustName',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="CustName" value="' . $_POST['CustName'] . '" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('Index NO') . ':</td>
			<td class="visible"><input ' . (in_array('index_no',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="index_no" value="' . $_POST['index_no'] . '" size=30 maxlength=40></td></tr>';			
echo '<tr><td class="visible">' . _('Age') . ':</td>
			<td class="visible"><input ' . (in_array('age',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="age" value="' . $_POST['age'] . '" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('Gender') . ':</td>
			<td class="visible"><input ' . (in_array('gender',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gender" value="' . $_POST['gender'] . '" size=30 maxlength=40></td></tr>';
echo '<tr><td class="visible">' . _('DoB') . ':</td>
			<td class="visible"><input ' . (in_array('dob',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="dob" value="' . $_POST['dob'] . '" size=30 maxlength=40></td></tr>';						
			
$sql2="SELECT gl.id,gl.grade_level 
FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
WHERE dm.id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$selected_grade=$myrow2['grade_level'];
$selected_id=$myrow2['id'];
	
	
echo '<tr><td class="visible">' . _('Stream') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='student_class'>";
		
$sql2="SELECT class_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_class_id=$myrow2['class_id'];
		
if(!isset($id) || $current_class_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stream');
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
	echo '<tr><td class="visible">' . _('Group') . ":</td>
		<td colspan=\"2\" class=\"visible\"><select name='student_group'>";
		
$sql2="SELECT group_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_group_id=$myrow2['group_id'];
		
if(!isset($id) || $current_group_id==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Group');
		$sql="SELECT id,status FROM status";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['status'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{

$sql3="SELECT id,status FROM status";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_group_id)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
		
	}
echo '<tr><td class="visible">' . _('Stage') . ":</td>
<td colspan=\"2\" class=\"visible\"><select name='estate'>";
		
$sql2="SELECT estate_id FROM debtorsmaster WHERE id='$id'";
$result2=DB_query($sql2,$db);
$myrow2 = DB_fetch_array($result2);
$current_estate=$myrow2['estate_id'];
		
if(!isset($id) || $current_estate==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Select Stage');
		$sql="SELECT id,estate FROM estates ORDER BY estate";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['estate'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>';
}
else{

$sql3="SELECT id,estate FROM estates";
$result3=DB_query($sql3,$db);
while(list($groupid, $selected_group) = DB_fetch_row($result3))
                {
        if ($groupid==$current_estate)
         {
          echo '<option selected value="' . $groupid . '">' .$selected_group. '</option>';
          }
         else
       {
        echo '<option value="' . $groupid . '">' . $selected_group. '</option>';
    }
   }
DB_data_seek($result3,0);
	echo '</select></td>';	
	}	
if (isset($id)) {
$sql = "SELECT transport,status FROM debtorsmaster WHERE id='" . $id . "'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['transport']=$myrow['transport'];
	$_POST['Blocked']=$myrow['status'];
}	
echo '<TR><td class="visible">' . _('Transport') . ":</TD><td class=\"visible\"><SELECT name='transport'>";
if ($_POST['transport']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}
'</SELECT></TD></TR>';		
		echo '</SELECT></TD></TR>';
echo '<TR><td class="visible">' . _('Status') . ":</TD><td class=\"visible\"><SELECT name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('Present');
	echo '<OPTION VALUE=1>' . _('Transfered/Completed');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Transfered/Completed');
	echo '<OPTION VALUE=0>' . _('Present');
}
echo '</select></td>';
       $result=DB_query('SELECT loccode, locationname FROM locations',$db);
			
		echo '</table></td><td class="visible"><table class="enclosed">';
		echo '<tr><td colspan="2" class="visible"><h3>Student Contact & Medical</h3></td></tr>';
		echo '<tr><td class="visible">' . _('Constituency') . ':</td>
			<td class="visible"><input ' . (in_array('boxno',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="boxno" size=30 maxlength=40 value="' . $_POST['boxno'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('County') . ':</td>
			<td class="visible"><input ' . (in_array('town',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="town" size=30 maxlength=40 value="' . $_POST['town'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('District') . ':</td>
			<td class="visible"><input ' . (in_array('zip',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="zip" size=20 maxlength=40 value="' . $_POST['zip'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Location') . ':</td>
			<td class="visible"><input ' . (in_array('state',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="state" size=30 maxlength=40 value="' . $_POST['state'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Sub-location') . ':</td>
			<td class="visible"><input ' . (in_array('mobileno',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="mobileno" size=30 maxlength=40 value="' . $_POST['mobileno'] . '"></td></tr>';
	echo '<tr><td class="visible">' . _('Disease (if any)') . ':</td>
			<td class="visible"><input ' . (in_array('disease',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="disease" size=30 maxlength=40 value="' . $_POST['disease'] . '"></td></tr>';
			echo '<tr><td class="visible">' . _('Previous School') . ':</td>
			<td class="visible"><input ' . (in_array('pschool',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="pschool" size=30 maxlength=40 value="' . $_POST['pschool'] . '"></td></tr>';
echo '<tr><td class="visible">' . _('House:') . '</TD><td class="visible"><SELECT Name="house">';
		$sql = 'SELECT * FROM houses';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['house']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['house'];
		} //end while loop
	echo '</SELECT></TD></TR>';					
	if(empty($passport)){
	echo "<tr><td class='visible'>Passport</td><TD class='visible'><input type='file' name='studentPhoto'></tr>";
	}	
	else{
	$_POST['passport']=$passport;
	}			
	echo '</table></td><td class="visible"><table class="enclosed">';
echo '<tr><td colspan="2" class="visible"><h3>Guardian Contact</h3></td></tr>';
echo '<tr><td class="visible">' . _('Relationship to Student') . ':</td>
			<td class="visible"><input ' . (in_array('relationship',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="relationship" size=30 maxlength=40 value="' . $_POST['relationship'] . '"></td></tr>';	
	echo '<tr><td class="visible">' . _('Full Name') . ':</td>
			<td class="visible"><input ' . (in_array('gname',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gname" size=30 maxlength=40 value="' . $_POST['gname'] . '"></td></tr>';
echo '<tr><td class="visible">' . _('Mobile No') . ':</td>
			<td class="visible"><input ' . (in_array('gmobileno',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gmobileno" size=30 maxlength=40 value="' . $_POST['gmobileno'] . '"></td></tr>';
	echo '<tr><td class="visible">' . _('P.O BOX') . ':</td>
			<td class="visible"><input ' . (in_array('gboxno',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gboxno" size=30 maxlength=40 value="' . $_POST['gboxno'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Town') . ':</td>
			<td class="visible"><input ' . (in_array('gtown',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gtown" size=30 maxlength=40 value="' . $_POST['gtown'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Postal Code') . ':</td>
			<td class="visible"><input ' . (in_array('gstate',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="gstate" size=30 maxlength=40 value="' . $_POST['gstate'] . '"></td></tr>';
			echo '<tr><td class="visible">' . _('Fee Balance') . ':</td>
			<td class="visible"><input ' . (in_array('balance',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="balance" size=30 maxlength=40 value="' . $_POST['balance'] . '"></td></tr>';
				
			
	}

	echo '</select></td></tr></table></td></tr>';
	if(isset($_POST['passport'])){	
	echo "<tr><td class='visible'>Passport</br>(150px * 110px)</td><td><img src=uploads/$passport </td><TD class='visible'>
	<input type='submit' name='removePassport' value='Remove'></td></tr>";
	}
	echo'</table>';

	if (isset($_POST['New']) and $_POST['New']) {
		echo "<div class='centre'><input type='Submit' name='submit' VALUE='" . _('Add New Student') .
			"'>&nbsp;<input type=submit name='reset' VALUE='" . _('Reset') . "'></div></form>";
	} else if (!isset($_GET['Modify'])){
		echo "<br><div class='centre'><input type='Submit' name='submit' VALUE='" . _('Update Student') . "'>";
		echo '&nbsp;<input type="Submit" name="delete" VALUE="' . _('Delete Student') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">';
	}
	
	echo '</div>';
} // end of main ifs

include('includes/footer.inc');
?>