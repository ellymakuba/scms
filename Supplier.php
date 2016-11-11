<?php
$PageSecurity = 5;
include('includes/session.inc');
$title = _('Supplier Registration Form');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['memberID'])){
	$memberID = strtoupper($_GET['memberID']);
} elseif (isset($_POST['memberID'])){
	$memberID = strtoupper($_POST['memberID']);
} else {
	unset($memberID);
}
echo '<p class="page_title_text">' . ' ' . $title.'</p>';
if(isset($_POST['removePassport']) && isset($memberID) && isset($_SESSION['passport']))
{
        $Passport=$_SESSION['passport'];
   		$target_path = "uploads/$Passport";
		echo $_SESSION['passport'];
		unlink($target_path);
		unset($_SESSION['passport']);
		$null='';		
		$sql = "UPDATE creditormaster SET photo='' WHERE id = '".$memberID."'";
		$ErrMsg = _('The Member passport could not be edited');
		$DbgMsg = _('The SQL that was used to update the member but failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		prnMsg(_('The member passport for') . ' ' . $memberID . ' ' . _('has been updated'),'success');
}
if(isset($_POST['removeIDCopy']) && isset($memberID) && isset($_SESSION['idCopy']))
{
        $IDCopy=$_SESSION['idCopy'];
   		$target_path = "uploads/$IDCopy";
		echo $_SESSION['idCopy'];
		unlink($target_path);
		unset($_SESSION['idCopy']);
		$null='';		
		$sql = "UPDATE creditormaster SET IDCopy='' WHERE id = '".$memberID."'";
		$ErrMsg = _('The Member IDCopy could not be edited');
		$DbgMsg = _('The SQL that was used to update the member but failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		prnMsg(_('The member IDCopy for') . ' ' . $memberID . ' ' . _('has been updated'),'success');
}
if(isset($_POST['removeSignature']) && isset($memberID) && isset($_SESSION['signature']))
{
        $Signature=$_SESSION['signature'];
   		$target_path = "uploads/$Signature";
		echo $_SESSION['signature'];
		unlink($target_path);
		unset($_SESSION['signature']);
		$null='';		
		$sql = "UPDATE creditormaster SET signature='' WHERE id = '".$memberID."'";
		$ErrMsg = _('The Member signature could not be edited');
		$DbgMsg = _('The SQL that was used to update the member but failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		prnMsg(_('The member signature for') . ' ' . $memberID . ' ' . _('has been updated'),'success');
}	
if (isset($_POST['memberSubmit'])) 
{
$target_path = "uploads/";
if(!empty($_FILES['IDCopy']['tmp_name'])){
$target_path_ID = $target_path . basename( $_FILES['IDCopy']['name']); 
	if(move_uploaded_file($_FILES['IDCopy']['tmp_name'], $target_path_ID)) {
		
	} 
	else{
		echo "<ul><li>There was an error uploading copy of ID.</li></ul>";
		 $InputError=1;
	}
}
if(!empty($_FILES['Signature']['tmp_name'])){	
	$target_path_signature = $target_path . basename( $_FILES['Signature']['name']); 
	if(move_uploaded_file($_FILES['Signature']['tmp_name'], $target_path_signature)) {
		
	} else{
		echo "<ul><li>There was an error uploading copy of signature.</li></ul>";
		 $InputError=1;
	} 
}
if(!empty($_FILES['Photo']['tmp_name'])){
	$target_path_employeePhoto = $target_path . basename( $_FILES['Photo']['name']); 
	if ((($_FILES["Photo"]["type"] == "image/gif")|| ($_FILES["Photo"]["type"] == "image/jpeg")|| ($_FILES["Photo"]["type"] == "image/jpg")
	|| ($_FILES["Photo"]["type"] == "image/png"))&& ($_FILES["file"]["size"] < 2000000))
	 {
	 if ($_FILES["Photo"]["error"] > 0) 
	 {
		echo "<ul><li>".$_FILES["Photo"]["error"]."</li></ul>";
		  $InputError=1;
	  } 
	  else
	   {
		if (file_exists("uploads/" . $_FILES["Photo"]["name"])) 
		{
		  echo "<ul><li> File already exists</li></ul>";
		  $InputError=1;
		} 
		else 
		{
		  move_uploaded_file($_FILES['Photo']['tmp_name'], $target_path_employeePhoto);
		}
	  }//end of else
	}//end of if 
	else {
	  echo "<ul><li>invalid file</li></ul>";
		  $InputError=1;
	}
}	
	
	
	
	
	
	
	
if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();	
      	   
       if ($_POST['name']=="")
       {
		   prnMsg(_('Supplier name must not be empty'),'error');
           $InputError=1;
		   $Errors[$i] = 'SuppName';
		    $i++;	
       }
	  /* if ($_POST['gender']=="")
       {
		   prnMsg(_('customer gender must be selected'),'error');
           $InputError=1;
		   $Errors[$i] = 'CustomerGender';
		    $i++;	
       }*/
	if ($InputError != 1){
	//	$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);
		if (!isset($_POST['New'])) {
				$sql = "UPDATE creditormaster SET
					name='" . DB_escape_string($_POST['name']) . "',				
					address1='" . DB_escape_string($_POST['Address1']) . "',
					address2='" . DB_escape_string($_POST['Address2']) . "',
					creditorNO='" . DB_escape_string($_POST['memberNO']) . "',
					city='" . DB_escape_string($_POST['City']) ."',
					gender='" . $_POST['Gender'] . "',
					active='" . $_POST['Active'] . "'
                WHERE id = '".$_POST['memberID']."'";
			$ErrMsg = _('The member could not be updated because');
			$DbgMsg = _('The SQL that was used to update the member but failed was');
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
			prnMsg(_('The member record for') . ' ' . $memberID . ' ' . _('has been updated'),'success');
			if(isset($_FILES['Photo']['name'])){
			$sql="UPDATE creditormaster SET Photo='" . $_FILES['Photo']['name'] . "' WHERE id = '$memberID'";
			$result = DB_query($sql, $db);
			}
			if(isset($_FILES['IDCopy']['name'])){
			$sql="UPDATE creditormaster SET IDCopy='" . $_FILES['IDCopy']['name'] . "' WHERE id = '$memberID'";
			$result = DB_query($sql, $db);
			}
			if(isset($_FILES['Signature']['name'])){
			$sql="UPDATE creditormaster SET signature='" . $_FILES['Signature']['name'] . "' WHERE id = '$memberID'";
			$result = DB_query($sql, $db);
			}

		} else { //its a new employee
       			$sql = "INSERT INTO creditormaster(creditorno,name,address1,address2,city,gender,active,Photo,
				nextOfKin,residence,IDCopy,signature)
				VALUES ('" . DB_escape_string($_POST['memberNO']) ."',
				'" . DB_escape_string($_POST['name']) ."',
				'" . DB_escape_string($_POST['Address1']) ."','" . DB_escape_string($_POST['Address2']) . "',
				'" . DB_escape_string($_POST['City']) . "','" . $_POST['Gender'] . "',
				'" . $_POST['Active'] . "','".$_FILES['Photo']['name']."','" . $_POST['nextOfKin'] . "',
				'" . $_POST['residence'] . "','" . $_FILES['IDCopy']['name'] . "','" . $_FILES['Signature']['name'] . "')";
			$ErrMsg = _('The member') . ' ' . $_POST['LastName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the member but failed was');
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
			prnMsg(_('A new member for') . ' ' . $_POST['LastName'] . ' ' . _('has been added to the database'),'success');
			unset ($memberID);
			unset($_POST['name']);
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['City']);
			unset($_POST['Gender']);
			unset($_POST['Active']);
			unset($_POST['memberNO']);
		}
		
	} else {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'),'warn');
	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {
	$CancelDelete = 0;	
		$sql = "SELECT creditor_id FROM purchaseorders
		 WHERE creditor_id='" . $memberID . "'";
		$EmpDetails = DB_query($sql,$db);
		if(DB_num_rows($EmpDetails)>0)
		{
			$CancelDelete = 1;
			prnMsg(_('This supplier has invoice(s) and so can not be deleted'),'error');
			exit();
		}	

	if ($CancelDelete == 0) {
		$sql="DELETE FROM creditormaster WHERE id='$memberID'";
		$result = DB_query($sql, $db);
		prnMsg(_('creditor record for') . ' ' . $memberID . ' ' . _('has been deleted'),'success');
		unset($memberID);
		unset($_SESSION['EmployeeID']);
	} //end if Delete employee
} //end of (isset($_POST['submit'])) 

if (!isset($memberID)) {
echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] .  '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo "<INPUT TYPE='hidden' NAME='New' VALUE='Yes'>";
	echo '</br><CENTER><TABLE class="enclosed"><tr><td><table class="enclosed">';
	echo '<tr><td class="tableTitle" colspan=2><h3>'._('Demographic Information').'</h3></td></tr>';
	echo '<TR><TD>' . _('Supplier NO') . ":</TD><TD><INPUT TYPE='text' NAME='memberNO'  SIZE=40 MAXLENGTH=40></TD></tr>";
	echo '<TR><TD class="visible">' . _('Supplier Name') . ":</TD><TD><input type='Text' name='name' size=50></TD></TR>";
	echo '<tr><td class="visible">'._('Gender').'</td><td class="visible"><input type="radio" 
	value="M" name="Gender">Male<input type="radio" 
	value="F" name="Gender">Female</td></tr>';		  
	echo '</table></td><td><table class="enclosed">';
	echo '<tr><td colspan=2><h3>'._('Contact Information').'</h3></td></tr>';
	echo '<TR><TD>' . _('Address') . ":</TD><TD class='visible'><input type='Text' name='Address1' </TD></TR>";	
	echo '<TR><TD>' . _('City') . ":</TD><TD class='visible'><input type='Text' name='City' </TD></TR>";
	echo '<tr><td >Next of kin</td><td class="visible"><input type="text" name="nextOfKin"></td></tr>';
	echo '<tr><td>Area of Residence</td><td class="visible"><input type="text" name="residence"></td></tr>';
	
	echo '<TR><TD><div align="right">' . _('Supplier Status') . ":</TD><TD><SELECT NAME='Active'>";	
	echo '<OPTION VALUE=0>' . _('Active');
	echo '<OPTION VALUE=1>' . _('InActive');
	echo '</SELECT></TD></TR>';
	
	echo '</table></td><td><table class="enclosed">';
	echo '<tr><td colspan=2><h3>'._('Attachments').'</h3></td></tr>';
	echo '<TR><td>Passport</br>(150px * 110px)</td><TD class="visible"><input type="file" name="Photo"></TD></TR>';
	echo '<TR><td>ID Copy</br>(100px * 70px)</td><TD class="visible"><input type="file" name="IDCopy"></TD></TR>';	
	echo '<TR><td>Signature Copy</br>(100px * 70px)</td><TD class="visible"><input type="file" name="Signature"></TD></TR>';
	echo "</TABLE></td></tr></table><p><CENTER><INPUT TYPE='Submit' NAME='memberSubmit' VALUE='" . _('Add New Supplier') . "'>";
	echo '</FORM>';
} 
else {
//EmployeeID exists - either passed when calling the form or from the form itself
	echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '</br><CENTER><TABLE class="enclosed"><tr><td><table class="enclosed">';
	echo "<INPUT TYPE=HIDDEN NAME='memberID' VALUE='$memberID'>";
	if (!isset($_POST['New'])) {
		$sql = "SELECT * FROM creditormaster WHERE id = '$memberID'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);
		$_POST['name'] = $myrow['name'];
		$_POST['Address1']  = $myrow['address1'];
		$_POST['Address2']  = $myrow['address2'];
		$_POST['City']  = $myrow['city'];
		$_POST['Gender']  = $myrow['gender'];	
		$_POST['Active']  = $myrow['active'];
		$_POST['nextOfKin']  = $myrow['nextOfKin'];
		$_POST['residence']  = $myrow['residence'];
		$_POST['memberNO']  = $myrow['debtorNO'];
		$passport=	$myrow['Photo'];
		$idCopy=	$myrow['IDCopy'];
		$signature=	$myrow['signature'];	
		echo "<INPUT TYPE=HIDDEN NAME='memberID' VALUE='".$memberID."'>";
		} 
		else {
		// its a new employee  being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
		echo '<TR><TD>' . _('Member ID') . ":</TD><TD><INPUT TYPE='text' NAME='memberNo' VALUE='".$_POST['memberNO']."'
		 SIZE=12 MAXLENGTH=10></TD></TR>";
		}
	echo '<tr><td class="tableTitle" colspan=2><h3>'._('Demographic Information').'</h3></td></tr>';
	 echo '<TR><TD>' . _('Supplier NO') . ":</TD><TD><INPUT TYPE='text' NAME='memberNO' size=40
	 value='" . $_POST['memberNO'] . "'></TD></tr>";
	echo '<TR><TD>' . _('Supplier Name') . ":</TD><TD class='visible'><input type='Text' name='name' size=50
	 value='" . $_POST['name'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Gender') . ":</TD><TD class='visible'><SELECT NAME='Gender'>";
	if ($_POST['Gender'] == 'M'){
		echo '<OPTION SELECTED VALUE="M">' . _('Male');
		echo '<OPTION VALUE="F">' . _('Female');
	} else {
		echo '<OPTION SELECTED VALUE="F">' . _('Female');
		echo '<OPTION VALUE="M">' . _('Male');
	}
	echo '</SELECT></TD></TR>';		  
	echo '</table></td><td><table class="enclosed">';
	echo '<tr><td colspan=2><h3>'._('Contact Information').'</h3></td></tr>';
	echo '<TR><TD>' . _('Address') . ":</TD><TD class='visible'><input type='Text' name='Address1' value='" . $_POST['Address1'] . "'></TD></TR>";	
	echo '<TR><TD>' . _('City') . ":</TD><TD class='visible'><input type='Text' name='City' value='" . $_POST['City'] . "'></TD></TR>";
	echo '<tr><td>Next of kin</td><td class="visible"><input type="text" name="nextOfKin" value="' . $_POST['nextOfKin'] . '"></td></tr>';
	echo '<tr><td>Area of Residence</td><td class="visible"><input type="text" name="residence" value="' . $_POST['residence'] . '"></td></tr>';
	
	echo '<TR><TD class="visible"><div align="right">' . _('Supplier Status') . ":</TD><TD><SELECT NAME='Active'>";		
	if ($_POST['Active'] == 0){
		echo '<OPTION SELECTED VALUE=0>' . _('Active');
		echo '<OPTION VALUE=1>' . _('InActive');
	} else {
		echo '<OPTION VALUE=0>' . _('Active');
		echo '<OPTION SELECTED VALUE=1>' . _('InActive');
	}
	echo '</SELECT></TD></TR>';
	
	echo '</table></td><td><table class="enclosed">';
	echo '<tr><td colspan=2><h3>'._('Attachments').'</h3></td></tr>';
	if(empty($passport)){
	echo "<tr><td class='visible'>Passport</td><TD class='visible'><input type='file' name='Photo'></tr>";
	}	
	else{
	$_SESSION['passport']=$passport;
	echo "<tr><td class='visible'>Passport</br>(150px * 110px)</td><td><img src=uploads/$passport </td><TD class='visible'><input type='submit' name='removePassport' value='Remove'></td></tr>";
	}
	if(empty($idCopy)){
	echo "<tr><td class='visible'>ID Copy</td><TD class='visible'><input type='file' name='IDCopy'></tr>";	
	}
	else{
	$_SESSION['idCopy']=$idCopy;
	echo "<tr><td class='visible'>ID Copy</br>(100px * 70px)</td><td><img src=uploads/$idCopy </td><TD class='visible'><input type='submit' name='removeIDCopy' value='Remove'></td></tr>";
	}
	if(empty($signature)){	
	echo "<tr><td class='visible'>Electronic Signature</td><TD class='visible'><input type='file' name='Signature'></tr>";	
	}
	else{
	$_SESSION['signature']=$signature;
	echo "<tr><td class='visible'>Electronic Signature</br>(100px * 70px)</td><td><img src=uploads/$signature </td><TD class='visible'><input type='submit' name='removeSignature' value='Remove'></td></tr>";
	}
	
	
	if (isset($_POST['New'])) {
		echo "</TABLE><P><CENTER><INPUT TYPE='Submit' NAME='memberSubmit' VALUE='" . _('Add New Supplier') . "'></FORM>";
	} 
	else {
		
		echo "</TABLE></td></tr></table><P><CENTER><INPUT TYPE='Submit' NAME='memberSubmit' VALUE='" . _('Update Supplier') . "'>";
		echo "<INPUT TYPE='Submit' NAME='delete' VALUE='" . _('Delete Customer') . "' onclick=\"return confirm('" . _('Are you sure you wish to delete this supplier?') . "');\"></FORM>";
	}

} // end of main ifs

include('includes/footer.inc');
?>