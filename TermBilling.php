<?php
ob_start();
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Termly Billing');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
if ($_POST['Bill']==_('Auto Invoice'))
{
if (!Is_Date($_SESSION['DateBanked'])){
	$_SESSION['DateBanked']= Date($_SESSION['DefaultDateFormat']);
	 
}
$PeriodNo = GetPeriod($_SESSION['DateBanked'],$db);
$sql = "SELECT term_status
		FROM collegeperiods
		WHERE id='".$_SESSION['period']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$Status=$myrow['term_status'];
if ($Status==1) {
   exit("Auto Invoice has already been run for this term. Re-open first...");
} else {  
		$current_year=date('Y');
		$sqlclass = "SELECT gl.* FROM gradelevels gl";
		$resultclass = DB_query($sqlclass,$db);	
		while ($myrowclass= DB_fetch_array($resultclass))
		{
		$sql = "SELECT ab.*,gl.grade_level FROM autobilling ab
		INNER JOIN  gradelevels gl ON gl.id=ab.class_id
		AND ab.term_id='".$_SESSION['period']."'
		AND gl.id='".$myrowclass['id']."'";
		$result = DB_query($sql,$db);
		$num_rows = DB_num_rows($result);
		if ($num_rows<0 || $num_rows==0) {
prnMsg(_($myrowclass['class_name']._(' ').'Fee Structure has not been created for this period'),'warn');
} else {	
		$sql = "SELECT ab.* FROM autobilling ab
		INNER JOIN  gradelevels gl ON gl.id=ab.class_id
		AND ab.term_id='".$_SESSION['period']."'
		AND gl.id='".$myrowclass['id']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$auto_id=$myrow['id'];
		
		$sql2 = "SELECT * FROM debtorsmaster
				WHERE grade_level_id='".$myrowclass['id']."'
				AND status=0";
				$result2 = DB_query($sql2,$db);
				$student_no=DB_num_rows($result2);
				if($student_no>0)
				{
					while ($myrow2= DB_fetch_array($result2))
					{
		$sql_exist = "SELECT id FROM salesorderdetails
		WHERE period_id='". $_SESSION['period'] ."'
		AND student_id='". $myrow2['id'] ."'";
		$result_exist=DB_query($sql_exist,$db);
	if(DB_fetch_row($result_exist)>0){
	prnMsg(_($myrow2['debtorno']._(' ').'has already been invoiced for this period'),'warn');	
	}
	else{
		$students=$myrow2['id'];
		$sql = "INSERT INTO salesorderdetails ( 	
		student_id,invoice_date,transactiondate,addedby,period_id)
		VALUES ('".$students."',
		'".date('Y-m-d H-i-s')."',
		'" . date('Y-m-d H-i-s'). "',
		'" . trim($_SESSION['UserID']) . "',
		'" . $_SESSION['period'] . "')";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('Unable to add the quotation line');
	$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql="SELECT LAST_INSERT_ID()";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	$id = $myrow[0];
	
	$glquery = "SELECT SUM(amount) as total FROM autobilling_items 
	WHERE autobilling_id='".$auto_id."'";
	$glresult = DB_query($glquery,$db);
	$glmyrow = DB_fetch_array($glresult);
	$glamount = $glmyrow['total'];
	$query = "INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							amount)
			VALUES (10,
				'".$id."',
				'".date('Y-m-d H-i-s')."',
				'" . $PeriodNo . "',
				1100,
				'".$glamount."')";
	$result = DB_query($query,$db);
	
	$query = "INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							amount)
										VALUES (10,
										'".$id."',
										'".date('Y-m-d H-i-s')."',
										'" . $PeriodNo . "',
										1,
										'".-$glamount."'
												)";
	$result = DB_query($query,$db);
	
	$sql3 = "SELECT * FROM autobilling_items 
		WHERE autobilling_id='".$auto_id."'";
		$result3 = DB_query($sql3,$db);		
	
	while($myrow3 = DB_fetch_array($result3))
	{	
	$sql = "INSERT INTO invoice_items ( invoice_id,product_id,
															qty,
															unitprice,
															totalinvoice,
															priority
															)
										VALUES ('".$id."',
												'".$myrow3['product_id']."',
												1,
												'".$myrow3['amount']."',
												'".$myrow3['amount']."',
												'".$myrow3['priority']."'
												)";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('Unable to add the quotation line');
	$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				}
			}				
		
		}
	}
}		
	}	
$sql = "UPDATE collegeperiods SET term_status=1
WHERE id = '".$_SESSION['period']."'";
$ErrMsg = _('The record could not be updated because');
$DbgMsg = _('The SQL that was used to update  was');
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
exit("Auto invoicing succeful...");
	
	}
}
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';
	
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
		echo '</select></td></tr>';
	DB_data_seek($result,0);
	echo '</select></td></tr></table>';
		echo '<table class="enclosed">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	

if (isset($_POST['submit'])) {
$_SESSION['class'] = $_POST['student_class'];
$_SESSION['period'] = $_POST['period'];
$sql2="SELECT t.title,cp.*,years.year FROM terms t
	INNER JOIN collegeperiods cp ON cp.term_id=t.id
	INNER JOIN years ON years.id=cp.year
	WHERE cp.id='".$_SESSION['period'] ."'";
	$result2=DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$term_status=$myrow2['term_status'];
	

echo "<TABLE class='enclosed'><TR><td>";
if($term_status==0){
echo "<INPUT TYPE=SUBMIT NAME='Bill' VALUE='" . _('Auto Invoice') . "'>";
}
echo "<INPUT TYPE=SUBMIT NAME='open' VALUE='" . _('Open Period') . "'></td></tr></table></BR>";

$sql2="SELECT t.title,cp.*,years.year FROM terms t
	INNER JOIN collegeperiods cp ON cp.term_id=t.id
	INNER JOIN years ON years.id=cp.year
	WHERE cp.id='".$_SESSION['period'] ."'";
	$result2=DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$term_name=$myrow2['title'];
	$year=$myrow2['year'];
	$term_status=$myrow2['term_status'];
	if($term_status==1){
	$term_status=_('Already Invoiced');
	}
	else{
	$term_status=_('Has Not Been Invoiced');
	}

?>
<table class="enclosed">
  <tr> 
    <td height="180" valign="top"> 
	
      <table class="enclosed">
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Term 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $term_name; ?><?php echo _(' '); ?><?php echo $year; ?></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Start Date 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow2['start_date'] ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">End Date 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow2['end_date']; ?></a></b></font></td>
        </tr>
        
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Period Status
              :</font></div>
          </td>
          <td height="30" width="74%" bgcolor="#F4F4F4"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1"><b><font color="#000066"><?php echo $term_status; ?></font></b></font></td>
        </tr>
      </table>
	  
    </td>
  </tr>

</table>
<?php	
echo '</table>';
}
include('includes/footer.inc');
?>
