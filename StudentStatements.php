<?php
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Receipt Entry');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$student_no = $_REQUEST['student_id'];
$msg='';
?>
<html><body><br /><br /><br />
<table class="enclosed"><form name="payment" action="CustomerReceipt.php" method="post">
<?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
?>
<?php
$sql = "SELECT SUM(amount) as total FROM invoice_items,salesorderdetails
WHERE salesorderdetails.id=invoice_items.invoice_id
AND salesorderdetails.student_id='".$student_no."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$studenttotal = $row['total'];

$sql = "SELECT SUM(ovamount) as totalpayment FROM debtortrans WHERE debtorno='".$student_no."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
 $ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$studenttotalpayment = -$row['totalpayment'];
$totalbalance=$studenttotal-$studenttotalpayment;

$sql = "SELECT id,name FROM debtorsmaster WHERE id='".$student_no."'";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$row = DB_fetch_array($result);
$student_id = $row['id'];
$name = $row['name'];
?>
<tr><th colspan="6"><font size="6" color="maroon">Student fee statement</font></th></tr>
<tr><td>Student Name:</td><td><?php echo $name  ?></td><td>Total Invoices:</td><td><?php echo number_format($studenttotal,2)  ?></td></tr>
<tr><td>Student RegNo:</td><td><?php echo $student_id  ?></td><td>Total Payments:</td><td><?php echo number_format($studenttotalpayment,2)  ?></td></tr>
<tr><td><?php echo $name  ?>'s Invoices</td><td>Balance:</td><td><?php echo number_format($totalbalance,2)  ?></td></tr>
<tr><th>Action</th><th>Total</th><th>Paid</th><th>Owing</th><th>Date</th></tr>
<?php
$sql = "SELECT	s.id,s.invoice_date,
(SELECT sum( COALESCE(ii.amount, 0)) FROM invoice_items ii WHERE ii.invoice_id=s.id) As invd,
(SELECT sum( COALESCE(dt.ovamount, 0)) FROM debtortrans dt where dt.transno = s.id) As pmt,
(SELECT COALESCE(invd, 0)) As total,
(SELECT COALESCE(pmt, 0)) As paid,
(select (total - paid)) as owing
FROM salesorderdetails s
WHERE 	s.student_id ='".$student_id."'	ORDER BY s.id DESC;";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
while ($row = DB_fetch_array($result))
{
    echo "<tr >";
    $ovamount=-$row['paid'];
    $balance=$row['total']-$ovamount;
     echo "<td class=\"visible\">"."<a href ='" . $rootpath ."/StudentReceipt.php?"."&invoice_id=" . $row['id']. "'>Pay</a>"."</td>";
     echo "<td class=\"visible\">".number_format($row['total'],2)."</td>";
 	echo "<td class=\"visible\">".number_format($ovamount,2)."</td>";
	  echo "<td class=\"visible\">".number_format($balance,2)."</td>";
	  echo "<td class=\"visible\">".$row['invoice_date']."</td>";
	    echo "</tr>";
$j++;
} ?>
</table>
<table class="enclosed">
<tr><th colspan="3"><?php echo $name  ?>'s Payments</th></tr>
<tr><th>Receipt NO</th><th>Amount</th><th>Date</th></tr>
<?php
$sql = "SELECT * FROM debtortrans WHERE debtorno='".$student_id."' ORDER BY id DESC";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
while ($row = DB_fetch_array($result))
{
	echo "<tr>";
	$ovamount=-$row['ovamount'];
	$balance=$row['totalinvoice']-$ovamount;
	echo "<td class=\"visible\">"."<a href ='" . $rootpath ."/PDFReceipt.php?"."&ReceiptNumber=" . $row['id']. "'>".	          $row['id']."</a>"."</td>";
	echo "<td class=\"visible\">".number_format($ovamount,2)."</td>";
	echo "<td class=\"visible\">".$row['trandate']."</td>";
	echo "</tr>";
	 $j++;
} ?>
</table><?php
include('includes/footer.inc');
?>
