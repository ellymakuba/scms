<?php
$PageSecurity = 2;
include('includes/session.inc');
include('includes/PDFStarter.php');
$FontSize=13;
$pdf->addinfo('Title', _('Sales Receipt') );

$PageNumber=1;
$line_height=12;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+200,$YPos-120,0,80);

$sql="SELECT transno FROM debtortrans WHERE id='".$_GET['ReceiptNumber'] ."'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$InvoioceNo=$myrow['transno'];

$sql="SELECT amount FROM invoice_items 
WHERE invoice_id='".$InvoioceNo ."'
AND product_id LIKE 'DISCOUNT'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$discount=$myrow['totalinvoice'];

$FontSize=13;
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*5),300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*6),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*8),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*5),550,$FontSize, _('Student Receipt Number ').'  : ' .$_GET['ReceiptNumber'] );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*6.5),550,$FontSize, _('Invoice No ').'  : ' .$InvoioceNo );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*7.5),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']));

$YPos -= 75;
$YPos -=$line_height;
$YPos -=(5*$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$FontSize=13;
$YPos -= (1.5 * $line_height);

$PageNumber++;
$sql="SELECT MIN(id) as start FROM debtortrans WHERE type=12 AND transno='".$_GET['BatchNumber']. "'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$StartReceiptNumber=$myrow['start'];

$sql="SELECT debtortrans.debtorno,debtortrans.ovamount,debtortrans.invtext,bankaccounts.bankaccountname  as bankname 
FROM debtortrans,banktrans,bankaccounts
WHERE debtortrans.type=12
AND banktrans.transno=debtortrans.transno
AND banktrans.bankact=bankaccounts.accountcode
AND debtortrans.id='".$_GET['ReceiptNumber'] ."'";
			
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$DebtorNo=$myrow['debtorno'];
$Amount2=$myrow['ovamount'];
$bankaccount = $myrow['bankname'];
$Narrative=$myrow['invtext'];

$sql="SELECT  id,name,debtorno FROM debtorsmaster
WHERE id='".$DebtorNo."'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$id=$myrow['id'];

$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Received From').' : ');
$LeftOvers = $pdf->addTextWrap(180,$YPos,300,$FontSize, htmlspecialchars_decode($myrow['name']));
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*1),300,$FontSize, _('Student AdmsnNo').' : ');
$LeftOvers = $pdf->addTextWrap(180,$YPos-($line_height*1),300,$FontSize, htmlspecialchars_decode($myrow['debtorno']));
$student=$myrow['debtorno'];

$YPos=$YPos-($line_height*1);
$YPos=$YPos-($line_height*2);

$pdf->line(45, $YPos+$line_height,400, $YPos+$line_height);
$YPos2=$YPos+$line_height;
$LeftOvers = $pdf->addTextWrap(50,$YPos,500,$FontSize,_('Product Name').' :');
$LeftOvers = $pdf->addTextWrap(200,$YPos,500,$FontSize,_('Amount').' :');
$LeftOvers = $pdf->addTextWrap(300,$YPos,500,$FontSize,_('Paid').' :');
$sql="SELECT invoice_items.*,stockmaster.description as descrip	
FROM invoice_items,stockmaster 
WHERE invoice_items.product_id=stockmaster.id
AND invoice_id='".$InvoioceNo ."'
ORDER BY invoice_items.priority,invoice_items.id";
$result=DB_query($sql, $db);
$product_balance=-$Amount2;
$product_paid=-$Amount2;
$remainder=0;
$rex=0;
$pdf->line(45, $YPos,400, $YPos);
while ($myrow=DB_fetch_array($result)){
$Product= $myrow['descrip'];
$amount= $myrow['amount'];
$amountPaid= $myrow['paid'];
$YPos -= $line_height;
$FontSize = 13;
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,htmlspecialchars_decode($Product));
$LeftOvers = $pdf->addTextWrap(200,$YPos,300,$FontSize,htmlspecialchars_decode($amount));
$LeftOvers = $pdf->addTextWrap(300,$YPos,300,$FontSize,htmlspecialchars_decode($amountPaid));
$pdf->line(45, $YPos,400, $YPos);

}
$pdf->line(45, $YPos2,45, $YPos);
$pdf->line(199, $YPos2,199, $YPos);
$pdf->line(299, $YPos2,299, $YPos);
$pdf->line(400, $YPos2,400, $YPos);
$sql = "SELECT SUM(amount) as total FROM invoice_items,salesorderdetails 
WHERE salesorderdetails.id=invoice_items.invoice_id
AND student_id='".$id."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$studenttotal = $row['total'];
			
$sql = "SELECT SUM(ovamount) as totalpayment FROM debtortrans WHERE debtorno='".$id."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$studenttotalpayment = -$row['totalpayment'];
$totalbalance=$studenttotal-$studenttotalpayment;

$YPos=$YPos-($line_height*1.5);
$FontSize = 11;
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Bank Account').' : ');
$LeftOvers = $pdf->addTextWrap(150,$YPos,300,$FontSize,$bankaccount);
$YPos=$YPos-($line_height*1.5);
$FontSize = 13;
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Amount Paid Now KSH').' : ');
$LeftOvers = $pdf->addTextWrap(250,$YPos,300,$FontSize,number_format(-$Amount2,$DecimalPlaces));
$YPos=$YPos-($line_height*1.5);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Discount KSH').' : ');
$LeftOvers = $pdf->addTextWrap(250,$YPos,300,$FontSize,number_format(-$discount,$DecimalPlaces));
$YPos=$YPos-($line_height*1.5);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Total Balance KSH').' : ');
$LeftOvers = $pdf->addTextWrap(250,$YPos,300,$FontSize,number_format($totalbalance,$DecimalPlaces));
$YPos=$YPos-($line_height*1.5);
$LeftOvers = $pdf->addTextWrap(50,$YPos,500,$FontSize,_('Signed On Behalf Of').' :     '.$_SESSION['CompanyRecord']['coyname']);


$YPos=$YPos-($line_height*2);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,'______________________________________________________________________________');
$YPos=$YPos-($line_height*1.5);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Note: Fees Once Paid Cannot Be Refunded.'));
$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
?>