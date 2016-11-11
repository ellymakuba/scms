<?php
include('includes/session.inc');
$title = _('Student Receipt Form');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text">' . ' ' . $title.'</p>';
$id=0;
$id = $_REQUEST['invoice_id'];
$msg='';
?>
<table class="enclosed"><form name="payment" action="StudentReceipt.php" method="post">
<?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$_POST['invoice_id']=$id;
?>
<tr><td>Invoice ID:</td><td class="visible"><input type="text" name="invoice_id" value="<?php echo $_POST['invoice_id'] ?>" readonly=""/>
<?php 
if (isset($id)) {
$sql = "SELECT SUM(amount) as totalInvoiceAmount FROM invoice_items WHERE invoice_id ='".$id."'";
$result = DB_query($sql,$db);
$row = DB_fetch_array($result);
$_POST['total_invoice'] = $row['totalInvoiceAmount'];			
$sql = "SELECT * FROM salesorderdetails WHERE id=$id";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$_POST['debtor_id'] = $row['student_id'];
}
?>
<tr><td class="visible">Invoice total:</td><td class="visible"><input type="text"  name="total_invoice" value="<?php echo $_POST['total_invoice'] ?>" readonly=""/>
<tr><td class="visible">Student:</td><td class="visible"><input type="text"  name="debtor_id" value="<?php echo $_POST['debtor_id'] ?>" readonly=""/>
<?php
$sql = "SELECT SUM(ovamount) as paidsum FROM debtortrans WHERE transno='".$id."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$paidsum=-$row['paidsum'];
if($paidsum<=0)
{
  $paidsum=0;
}
?>
<tr><td class="visible">Prevoius Payments:</td><td class="visible"><input type="text" 
 name="amount_paid" value="<?php echo $paidsum ?>" readonly=""/>
<tr><td class="visible">Bank Account:</td><td class="visible"><select  name="account_code" >
<?php 
if(isset($_POST['account_code']) && $_POST['account_code'] !=0){
	$SQL = "SELECT 	accountcode,bankaccountname FROM bankaccounts
	WHERE accountcode='".$_POST['account_code']."'";
	$result= DB_query($SQL,$db);
	$myrow=DB_fetch_array($result);
	echo '<option selected value="'.$myrow['accountcode'].'">'.$myrow['bankaccountname'];
}
else{
echo '<option selected value=0>'._('Select bank account');
}
$SQL = "SELECT 	bankaccounts.accountcode,bankaccounts.bankaccountname FROM bankaccounts,chartmaster
WHERE bankaccounts.accountcode=chartmaster.accountcode";
$ErrMsg =_('The bank account name cannot be retrieved because');
$result= DB_query($SQL,$db,$ErrMsg);
while(list($accountcode, $accountname) = DB_fetch_row($result))
{ 
echo '<option value="' . $accountcode . '">' . $accountname . '</option>'; 
}		
?></select>
</td></tr>
<tr><td class="visible">Payment Method:</td><td class="visible"><select  name="payment_method" >
<?php 
include('includes/GetPaymentMethods.php');
foreach ($ReceiptTypes as $RcptType) {
if (isset($_POST['ReceiptType']) and $_POST['ReceiptType']==$RcptType){
		echo "<option selected Value='$RcptType'>$RcptType";
}
else {
 echo "<option Value='$RcptType'>$RcptType";
}
}		
?></select>
</td></tr>
<tr><td class="visible">Amount:</td><td class="visible"><input type="text"  name="amount" value="<?php echo $_POST['amount'] ?>"></td></tr>
<tr><td class="visible">Date(D/M/Y):</td><td class="visible"><input type="text"  name="payment_date"  class="date" value="<?php echo date('d/m/Y') ?>" >
<tr><td class="visible">Notes:</td><td class="visible"><textarea name="notes" rows="1" cols="100"></textarea></td></tr>
</td></tr>
<tr><td><input type="submit"  name="payment" onClick="confirmation()" value="submit"></td></tr>
</form></table><br>
<?php

$_SESSION['DateBanked']= Date($_SESSION['DefaultDateFormat']);
$SQL = "SELECT currabrev FROM currencies,debtorsmaster WHERE debtorsmaster.currcode=currencies.currabrev
	AND debtorsmaster.debtorno='" . $_POST['student_id']."'";
	$ErrMsg =_('The currency name cannot be retrieved because');
	$result= DB_query($SQL,$db,$ErrMsg);
	$row = DB_fetch_row($result);
	$currcode=$row[0]; 
	$_SESSION['Currency']=$currcode;	
$PeriodNo = GetPeriod($_SESSION['DateBanked'],$db);
$_SESSION['payment_date']=$_POST['payment_date'];	
	
if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;	
	
if (isset($_POST['payment'])) {

	$i=1;
	
	if (empty($_SESSION['payment_date'])) {
		$InputError = 1;
		prnMsg( _('Please enter a validate date'),'error');
		$Errors[$i] = 'payment_date';
		$i++;
	}
	else if(($_POST['account_code']) ==0 ){
		$InputError = 1;
		prnMsg( _('Please Select a Bank account'),'error');
		$Errors[$i] = 'account_code';
		$i++;
	}
	else if(($_POST['total_invoice']) < ($_POST['amount']+$_POST['amount_paid']) )
	{
		$InputError = 1;
		prnMsg( _('The total payments cannot exceed invoiced amount'),'error');
		$Errors[$i] = 'payment_date';
		$i++;
	}
	else if($InputError==0)
	{
	$sql = "INSERT INTO debtortrans( transno,type,debtorno,trandate,inputdate,prd,ovamount,addedby,invtext)
	VALUES ('".$_POST['invoice_id']."',12,'".$_POST['debtor_id']."','".FormatDateForSQL($_SESSION['payment_date'])."',
	'" . date('Y-m-d H-i-s') . "','".$PeriodNo."','".-$_POST['amount']."','" . trim($_SESSION['UserID']) . "','".$_POST['notes']."')";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('Unable to add the quotation line');
	$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	
	
	$query="INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount)
	VALUES (12,'" .$_POST['invoice_id'] . "','".FormatDateForSQL($_POST['payment_date'])."','" . $PeriodNo . "',
	'1','" . $_POST['notes'] . "','" . $_POST['amount'] . "')";
	$result = DB_query($query,$db);
			
	$query="INSERT INTO gltrans ( type,typeno,trandate,periodno,account,narrative,amount)
	VALUES (12,'" .$_POST['invoice_id'] . "','".FormatDateForSQL($_SESSION['payment_date'])."','" . $PeriodNo . "',1100,'" . $_POST['notes'] . "',
	'" .-$_POST['amount'] . "'
	)";
	$result = DB_query($query,$db);
			
	$SQL="INSERT INTO banktrans (type,transno,bankact,ref,exrate,functionalexrate,transdate,banktranstype,amount,inputdate,addedby,currcode)
	VALUES (12,'".$_POST['invoice_id']."','" . $_POST['account_code']. "','" . $_POST['notes'] . "',1,1,'".$_SESSION['payment_date']."','" .$_POST['payment_method']  . "',
	'" .$_POST['amount']  . "','" . date('Y-m-d') . "','" . trim($_SESSION['UserID']) . "','" . $_SESSION['Currency'] . "')";
	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Cannot insert a bank transaction');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			
	$sql="SELECT invoice_items.*,stockmaster.description as descrip	
	FROM invoice_items,stockmaster 
	WHERE invoice_items.product_id=stockmaster.id
	AND invoice_id='".$_POST['invoice_id'] ."'
	ORDER BY invoice_items.priority";
	$result=DB_query($sql, $db);
	
	$previousAmountPaidForProduct=0;
	$amountPaidOnReceipt=$_POST['amount'];
	while ($myrow=DB_fetch_array($result))
	{
		$previousAmountPaidForProduct=$myrow['paid'];
		$amountOnInvoice= $myrow['amount'];
		if($amountPaidOnReceipt>0)
		{
			if($previousAmountPaidForProduct == $amountOnInvoice)
			{
			}
			else
			{
				$balance=$amountOnInvoice-$previousAmountPaidForProduct;
				$amountPaidOnReceipt=$amountPaidOnReceipt-$balance;
				if($amountPaidOnReceipt>0  || $amountPaidOnReceipt==0)
				{
					$sql = "UPDATE invoice_items SET paid=paid +'".$balance."'
					WHERE id='".$myrow['id']."'";
					$query = DB_query($sql,$db);
				}
				else
				{
					$amountPaidOnReceipt=$amountPaidOnReceipt+$balance;
					$sql = "UPDATE invoice_items SET paid=paid +'$amountPaidOnReceipt'
					WHERE id='".$myrow['id']."'";
					$query = DB_query($sql,$db);
					$amountPaidOnReceipt=0;
				}		
			}
		}	
	}
	 echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath ."/ManagePayments.php". "'>";
	}
}	
include('includes/footer.inc');
?>
