<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 3;
include('includes/session.inc');

$title = _('Receipt Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$parent_no = $_REQUEST['mobileno'];
$msg='';
?>
<html><body><br /><br /><br />
<table width="50%"><form name="payment" action="ParentStatement.php" method="post">
<?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
?>
<?php 
$sql = "SELECT SUM(totalinvoice) as total FROM invoice_items,salesorderdetails 
		INNER JOIN debtorsmaster dm ON dm.id=salesorderdetails.student_id
		AND dm.gmobileno='".$parent_no."'
		WHERE salesorderdetails.id=invoice_items.invoice_id
		";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');

            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

            $row = DB_fetch_array($result);
			$studenttotal = $row['total'];
			
$sql = "SELECT SUM(ovamount) as totalpayment FROM debtortrans 
		INNER JOIN debtorsmaster dm ON dm.id=debtortrans.debtorno
		WHERE dm.gmobileno='".$parent_no."'";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');

            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

            $row = DB_fetch_array($result);
			$studenttotalpayment = -$row['totalpayment'];
			$totalbalance=$studenttotal-$studenttotalpayment;
$sql = "SELECT gmobileno,gname FROM debtorsmaster WHERE gmobileno='".$parent_no."'";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');

            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

            $row = DB_fetch_array($result);
			$name = $row['gname'];
	
?>
<tr><th colspan="6"><font size="6" color="maroon">Parent fee statement</font></th></tr>
<tr><td>Parent Name:</td><td><?php echo $name  ?></td><td>Total Invoices:</td><td><?php echo number_format($studenttotal,2)  ?></td></tr>
<tr><td>Parent Mobile No:</td><td><?php echo $parent_no  ?></td><td>Total Payments:</td><td><?php echo number_format($studenttotalpayment,2)  ?></td></tr>
<tr><td><?php echo $name  ?>'s Invoices</td><td>Balance:</td><td><?php echo number_format($totalbalance,2)  ?></td></tr>
<tr><th>ID</th><th>Total</th><th>Paid</th><th>Owing</th><th>Date</th></tr>
<?php
$sql = "SELECT	
		s.id, 
		s.invoice_date,
		(SELECT sum( COALESCE(ii.totalinvoice, 0)) FROM invoice_items ii WHERE ii.invoice_id=s.id) As invd,
		(SELECT sum( COALESCE(dt.ovamount, 0)) FROM debtortrans dt where dt.transno = s.id) As pmt,
		(SELECT COALESCE(invd, 0)) As total, 
		(SELECT COALESCE(pmt, 0)) As paid, 
		(select (total - paid)) as owing 
	FROM 
		salesorderdetails s ,debtorsmaster dm
	WHERE 
		dm.id=s.student_id
		AND 
		dm.gmobileno ='".$parent_no."'
	ORDER BY 
		s.id DESC;";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			while ($row = DB_fetch_array($result))
			{
			
		    echo "<tr>";
			$ovamount=-$row['paid'];
			$balance=$row['total']-$ovamount;
		  echo "<td class=\"visible\">".$row['id']."</td>";
		  echo "<td class=\"visible\">".number_format($row['total'],2)."</td>";
		  echo "<td class=\"visible\">".number_format($ovamount,2)."</td>";
		  echo "<td class=\"visible\">".number_format($balance,2)."</td>";
		  echo "<td class=\"visible\">".$row['invoice_date']."</td>";
		    echo "</tr>";
		  $j++;
			} ?>
</table>
<table width="50%">
<tr><th colspan="3"><?php echo $name  ?>'s Payments</th></tr>
<tr><th>Receipt NO</th><th>Amount</th><th>Date</th></tr>
<?php
$sql = "SELECT debtortrans.* FROM debtortrans
INNER JOIN debtorsmaster dm ON dm.id=debtortrans.debtorno
		WHERE dm.gmobileno='".$parent_no."'
		ORDER BY debtortrans.id DESC";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			while ($row = DB_fetch_array($result))
			{
			
		    echo "<tr >";
			$ovamount=-$row['ovamount'];
			$balance=$row['totalinvoice']-$ovamount;
		   echo "<td class=\"visible\">".$row['id']."</td>";
		  echo "<td class=\"visible\">".number_format($ovamount,2)."</td>";
		  echo "<td class=\"visible\">".$row['trandate']."</td>";
		    echo "</tr>";
		  $j++;
			} ?>
			
			</table>
			<table width="50%"><tr><th colspan="4"><?php echo $name  ?>'s Children</th></tr>
			<tr><th>AdmsnNO</th><th>Student Name</th><th>Stream</th><th>Balance</th></tr>
			<?php
$sql = "SELECT debtorsmaster.id as SID, debtorsmaster.debtorno,
					debtorsmaster.name as name, classes.class_name,
					
					(
						SELECT
				            coalesce(sum(invoice_items.totalinvoice),  0) AS total 
				        FROM
				            invoice_items  INNER JOIN
				            salesorderdetails ON (salesorderdetails.id = invoice_items.invoice_id)
				        WHERE  
				            salesorderdetails.student_id  = SID ) as student_total,
	                (
	                    SELECT 
	                        coalesce(-sum(debtortrans.ovamount), 0) AS amount
	                    FROM
	                        debtortrans INNER JOIN
	                        salesorderdetails  ON (salesorderdetails.id = debtortrans.transno)
	                    WHERE 
	                        salesorderdetails.student_id = SID) AS paid,
							
	                ( select student_total - paid ) AS owing
	
				FROM 
				debtorsmaster
				INNER JOIN classes ON classes.id=debtorsmaster.class_id
				WHERE gmobileno='".$parent_no."'";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			while ($row = DB_fetch_array($result))
			{
			
		    echo "<tr >";
		   echo "<td class=\"visible\">".$row['debtorno']."</td>";
		  echo "<td class=\"visible\">".$row['name']."</td>";
		  echo "<td class=\"visible\">".$row['class_name']."</td>";
		  echo "<td class=\"visible\">".number_format($row['owing'],2)."</td>";
		    echo "</tr>";
		  $j++;
			} ?>
			
			</table><br><?php
include('includes/footer.inc');
?>
