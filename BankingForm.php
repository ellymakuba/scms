<?php
$PageSecurity = 5;
include('includes/session.inc');
$title = _('Banking Form');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$memberID = $_REQUEST['memberID'];

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Bank') 
. '" alt="">' . ' ' . $title . '</p>';
if ( isset($_GET['bankingID']) )
	$bankingID = $_GET['bankingID'];
elseif (isset($_POST['bankingID']))
	$bankingID = $_POST['bankingID'];
	
if(isset($Errors)) 
{
	unset($Errors);
}
$Errors = array();
if (isset($_POST['submit'])){
	$InputError = 0;
	if ($_POST['bankAccountCode']==0) {
		$InputError = 1;
		prnMsg(_('bankAccountCode must be selected please'),'error');
		$Errors[$i] = 'bankCode';
		$i++;
	}
	if ($_POST['paymentMode']==0) {
		$InputError = 1;
		prnMsg(_('payment mode must be selected please'),'error');
		$Errors[$i] = 'pMode';
		$i++;
	}
	if ($_POST['transactionType']==0) {
		$InputError = 1;
		prnMsg(_('Transaction Type must be selected please'),'error');
		$Errors[$i] = 'TransType';
		$i++;
	}
	if ($_POST['amount']=="") {
		$InputError = 1;
		prnMsg(_('amount field cannot be empty'),'error');
		$Errors[$i] = 'AmountDetails';
		$i++;
	}
	if ($_POST['approvedBy']=="") {
		$InputError = 1;
		prnMsg(_('approved By field cannot be empty'),'error');
		$Errors[$i] = 'approver';
		$i++;
	}
	if ($_POST['entryDate']=="") {
		$InputError = 1;
		prnMsg(_('entryDate field cannot be empty'),'error');
		$Errors[$i] = 'dateEntered';
		$i++;
	}	
	if ($InputError != 1){
	    if (!Is_Date($_SESSION['DateBanked']))
		{
			$_SESSION['DateBanked']= Date($_SESSION['DefaultDateFormat']);
						 
		}
		$PeriodNo = GetPeriod($_SESSION['DateBanked'],$db);
		$TransNo = GetNextTransNo(0,$db);
		$SQL_entryDate = FormatDateForSQL($_POST['entryDate']);
		if (!isset($_POST['New'])) 
		{
			$sql="SELECT amount,transaction_type FROM  banking WHERE banking_id='$bankingID'";
			$result=DB_query($sql,$db);
			$myrow=DB_fetch_row($result);
			$initialAmountPaid=$myrow[0];
			$previousTransactionType=$myrow[1];
			
			$sql = "UPDATE banking SET			
			entryDate='$SQL_entryDate',
			bank_account_code='" . DB_escape_string($_POST['bankAccountCode']) ."',
			amount='" . DB_escape_string($_POST['amount']) ."',
			payment_mode='" . DB_escape_string($_POST['paymentMode']) ."',
			approved_by='" . DB_escape_string($_POST['approvedBy']) ."',
			comment='" . DB_escape_string($_POST['comment']) ."'
            WHERE banking_id = '$bankingID'";
			$ErrMsg = _('The record could not be updated because');
			$DbgMsg = _('The SQL that was used to update the member saving but failed was');
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
			prnMsg(_('The record for') . ' ' . $bankingID . ' ' . _('has been updated'),'success');
			
			$difference=$initialAmountPaid-$_POST['amount'];
			if($previousTransactionType==1){
			if($difference > 0)
			{
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".-$difference."')";
				$result = DB_query($sql, $db);
				
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".$difference."')";
				$result = DB_query($sql, $db);
			}
			else if($difference < 0)
			{
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".-$difference."')";
				$result = DB_query($sql, $db);
				
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".$difference."')";
				$result = DB_query($sql, $db);
			}
		 }
		 else{//it was a withdrawal transaction type
		 
		  if($difference > 0)
			{
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".$difference."')";
				$result = DB_query($sql, $db);
				
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".-$difference."')";
				$result = DB_query($sql, $db);
			}
			else if($difference < 0)
			{
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".$difference."')";
				$result = DB_query($sql, $db);
				
				$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	            VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".-$difference."')";
				$result = DB_query($sql, $db);
			}
		 }
		} 
		else 
		{ //its a new employee
			$sql = "INSERT INTO banking (entryDate,bank_account_code,amount,transaction_type,payment_mode,approved_by,comment)
			VALUES ('" . $SQL_entryDate . "','" . DB_escape_string($_POST['bankAccountCode']) . "',
			'" . DB_escape_string($_POST['amount']) ."','" . DB_escape_string($_POST['transactionType']) ."',
			'" . DB_escape_string($_POST['paymentMode']) ."',
			'" . DB_escape_string($_POST['approvedBy']) ."','" . DB_escape_string($_POST['comment']) ."')";
			$ErrMsg = _('The record could not be added because');
			$DbgMsg = _('The SQL that was used to insert the savings but failed was');
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
			prnMsg(_('A record has been effected'),'success');
			
			if($_POST['transactionType']==1){
			$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	        VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".$_POST['amount']."')";
			$result = DB_query($sql, $db);
				
			$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	        VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".-$_POST['amount']."')";
			$result = DB_query($sql, $db);
			}
			else
			{
			$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	        VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "','".$_POST['bankAccountCode']."','".-$_POST['amount']."')";
			$result = DB_query($sql, $db);
				
			$sql = "INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount)
	        VALUES (10,'".$TransNo."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1020,'".$_POST['amount']."')";
			$result = DB_query($sql, $db);
			}
			unset ($LoanFileId);
			unset($_POST['bankAccountCode']);
			unset($_POST['TransactionType']);
			unset($_POST['paymentMode']);
			unset($_POST['approvedBy']);			
			unset($_POST['comment']);
			unset($_POST['amount']);
		}
		
	}
	 else 
	 {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'),'warn');
	}
}
if (!isset($bankingID))
 {
    echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo "<INPUT TYPE='hidden' NAME='New' VALUE='Yes'>";
	echo '<input type=hidden name=bankAccountCode VALUE=' . $_POST['bankAccountCode'] . '>';
	echo '<BR><CENTER><TABLE class="enclosed">';
	echo '<tr><td class="visible">' . _('Account Code') . ":</td>
	<td class=\"visible\"><select tabindex='1' " . (in_array('bankCode',$Errors) ?  'class="selecterror"' : '' ) ."  name='bankAccountCode'>";
	
	$sql="SELECT accountcode,bankaccountname from bankaccounts WHERE code=0";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result))
	 {
		 if (isset($_POST['bankAccountCode']) and $myrow['accountcode']==$_POST['bankAccountCode']) 
		 {
			echo '<option selected VALUE=';
		 }
		 else 
		 {
			echo '<option VALUE=';
		  }
		echo $myrow['accountcode'] . '>' . $myrow['bankaccountname'];
	} //end while loop
	echo '</select></td></tr>';	
	echo '<tr><td>' . _('Transaction Type') . ":</td>
	<td><select name='transactionType'>";
	echo '<OPTION SELECTED VALUE=0>' . _('Select Transaction Type');
	echo '<option value=1>'. _('Deposit');
	echo '<option value=2>'. _('Withdrawal');
	echo '</select></td></tr>';
	echo '<tr><td class="visible">' . _('Transaction Mode') . ":</td>
	<td class=\"visible\"><select name='paymentMode'>";
	echo '<OPTION SELECTED VALUE=0>' . _('Select Mode');
	$sql="SELECT * FROM paymentmethods";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result))
	 {
      echo '<option value='. $myrow['paymentid'] . '>'.$myrow['paymentname'];
	} //end while loop
	echo '</select></td></tr>';
	echo '<TR><TD>' . _('Amount') . ":</TD><TD><INPUT tabindex='1' " . (in_array('AmountDetails',$Errors) ?  'class="selecterror"' : '' ) ."
	TYPE='text' NAME='amount' value='".$_POST['amount']."'></TD></TR>";
	echo '<TR><TD>' . _('Approved By') . ":</TD><TD><INPUT tabindex='1' " . (in_array('approver',$Errors) ?  'class="selecterror"' : '' ) ."
	TYPE='text' NAME='approvedBy' value='".$_POST['approvedBY']."' ></TD></TR>";
	echo '<TR><TD>' . _('Comments') . ":</TD><TD><INPUT TYPE='text' NAME='comments' size=100></TD></TR>";
	$DateString = Date($_SESSION['DefaultDateFormat']);	
	echo '<TR><TD>' . _('Entry Date') . ' (' . $_SESSION['DefaultDateFormat'] . "):
	</TD><TD><input tabindex='1' " . (in_array('dateEntered',$Errors) ?  'class="selecterror"' : '' ) ."
	type='Text' name='entryDate' value=$DateString SIZE=12 MAXLENGTH=10></TD></TR>";
	echo "</TABLE><p><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Enter Transaction') . "'>";
	echo '</FORM><BR>';
	
	$sql = "SELECT banking_id,entrydate,amount,approved_by FROM banking  ORDER BY banking_id";
	$ErrMsg = _('The records could not be retrieved because');
	$result = DB_query($sql,$db,$ErrMsg);
	echo '<BR><CENTER><table class="enclosed">';
	echo "<tr>
		<td class='tableheader'>" . _('Banking ID') . "</td>
		<td class='tableheader'>" . _('Entry Date') . "</td>
		<td class='tableheader'>" . _('Amount') . "</td>
		<td class='tableheader'>" . _('Approved BY') . "</td>
	</tr>";
	$totalSavings=0;
	while ($myrow = DB_fetch_row($result))
	 {
		echo '<TD>' . $myrow[0] . '</TD>';
		echo '<TD>' . $myrow[1] . '</TD>';
		echo '<TD>' . $myrow[2] . '</TD>';
		echo '<TD>' . $myrow[3] . '</TD>';
		echo '<TD><A HREF="'. $rootpath . '/BankingForm.php?' . SID . '&bankingID=' . $myrow[0] . 
		'&memberID='.$memberID.'">' . _('Edit') . '</A></TD>';
		echo '</TR>';
		$totalSavings=$totalSavings+$myrow[2];
	} 
	echo '<TR><TD>Total</TD><TD></TD><TD></TD><TD>' . $totalSavings . '</TD></TR></table>';

} 
else 
{		
         echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
         echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	     echo '<CENTER><TABLE class="enclosed">';
		 if (!isset($_POST['New'])) 
		 {
		    $sql = "SELECT * FROM banking
			WHERE banking_id = '$bankingID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
		    $_POST['bankingID'] = $myrow['banking_id'];
			$_POST['bankAccountCode'] = $myrow['bank_account_code'];
			$_POST['amount'] = $myrow['amount'];	
			$_POST['entryDate'] = ConvertSQLDate($myrow['entryDate']);
			$_POST['transactionType']  = $myrow['transaction_type'];
			$_POST['approvedBy']  = $myrow['approved_by'];
			$_POST['paymentMode']  = $myrow['payment_mode'];
			$_POST['comment']  = $myrow['comment'];				
			echo "<INPUT TYPE=HIDDEN NAME='bankingID' VALUE='$bankingID'>";
		} 
		else 
		{			
			echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
	     }
	echo '<TR><TD>' . _('Banking ID') . ":</TD><TD><INPUT TYPE='text' NAME='bankingID'
	 value='".$_POST['bankingID']."' readonly=''></TD></TR>";
	echo '<tr><td class="visible">' . _('Account Code') . ":</td>
	<td class=\"visible\"><select name='bankAccountCode'>";
	if($_POST['bankAccountCode'] !=0){
	
	echo "<OPTION SELECTED VALUE='".$_POST['bankAccountCode']."'>" .$_POST['bankAccountCode'];
	}
	else{
	echo '<OPTION SELECTED VALUE=0>' . _('Select Bank Account');
	}	
	$sql="SELECT accountcode,bankaccountname from bankaccounts WHERE code=0";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result))
	 {
      echo '<option value='. $myrow['accountcode'] . '>'.$myrow['bankaccountname'];
	} //end while loop
	echo '</select></td></tr>';	
	echo '<tr><td>' . _('Transaction Type') . ":</td><td><select name='transactionType' readonly=''>";
	if($_POST['transactionType'] !=0){
	 if($_POST['transactionType']==1){
	 echo "<OPTION SELECTED VALUE='".$_POST['transactionType']."'>" ._('Deposit');
	 }
	 else{
	 echo "<OPTION SELECTED VALUE='".$_POST['transactionType']."'>" ._('Withdrawal');
	 }
	
	}
	else{
	echo '<OPTION SELECTED VALUE=0>' . _('Select Transaction Type');
	}
	echo '<option value=1>'. _('Deposit');
	echo '<option value=2>'. _('Withdrawal');
	echo '</select></td></tr>';
	echo '<tr><td class="visible">' . _('Transaction Mode') . ":</td><td class=\"visible\"><select name='paymentMode'>";
	if($_POST['paymentMode'] !=0){
	$sql = "SELECT paymentname from paymentmethods WHERE paymentid = '".$_POST['paymentMode']."'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	echo "<OPTION SELECTED VALUE='".$_POST['paymentMode']."'>" .$myrow[0];
	}
	else{
	echo '<OPTION SELECTED VALUE=0>' . _('Select Payment Mode');
	}
	$sql="SELECT * FROM paymentmethods";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result))
	 {
      echo '<option value='. $myrow['paymentid'] . '>'.$myrow['paymentname'];
	} //end while loop
	echo '</select></td></tr>';
	echo '<TR><TD>' . _('Amount') . ":</TD><TD><INPUT TYPE='text' NAME='amount' value='".$_POST['amount']."'></TD></TR>";
	echo '<TR><TD>' . _('Approved By') . ":</TD><TD><INPUT TYPE='text' NAME='approvedBy' size=20 value='".$_POST['approvedBy']."'></TD></TR>";
	echo '<TR><TD>' . _('Comments') . ":</TD><TD><INPUT TYPE='text' NAME='comments' size=100 value='".$_POST['comments']."'></TD></TR>";
	echo '<TR><TD>' . _('Entry Date') . ' (' . $_SESSION['DefaultDateFormat'] . "):
	</TD><TD><input type='Text' name='entryDate' value='".$_POST['entryDate']."'></TD></TR>";
	if (isset($_POST['New'])) {
		echo "</TABLE><P><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Enter banking Details') . "'></FORM>";
	} 
	else 
	{
		echo "</TABLE><P><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Update banking Details') . "'>";
		echo "</FORM>";
	}

} 
include('includes/footer.inc');
?>