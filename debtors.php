<?php

/* $Revision: 1.17 $ */
/* $Id: CustomerTransInquiry.php 3870 2010-09-30 14:54:21Z tim_schofield $*/

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Student Balance');
include('includes/header.inc');
echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


 ?>
<table width="70%" >
<tr><th><font size="4" color="Blue">Admission No</font></th><th><font size="4" color="Blue">Name</font></th><th><font size="4" color="Blue">Total Invoices</font></th><th><font size="4" color="Blue">Total Payments</font></th><th><font size="4" color="Blue">Balance</font></th><th><font size="4" color="Blue">Percentage Balance</font></th></tr>
<?php
 if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT count(*) FROM debtorsmaster";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "debtors.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	

$sql = "SELECT sum(totalinvoice) AS total_invoiced  FROM  invoice_items ";
$result = DB_query($sql,$db);
$row = DB_fetch_array($result);
$college_total=$row['total_invoiced'];

$sql = "SELECT -sum(ovamount) AS total_paid  FROM  debtortrans ";
$result = DB_query($sql,$db);
$row = DB_fetch_array($result);
$college_paid=$row['total_paid'];

$college_balance=$college_total-$college_paid;

$sql = "SELECT 
					debtorsmaster.id as SID,debtorsmaster.debtorno, 
					debtorsmaster.name as name, 
					
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
	                ( select student_total - paid ) AS owing,
					( select  (1-(paid/student_total))*100 ) AS percentage
	
				FROM 
					debtorsmaster
	ORDER BY 
		percentage DESC $limit;";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$balance=0;
			while ($row = DB_fetch_array($result))
			{
			$percent=number_format($row['percentage'],2);
			
		  echo "<td class=\"visible\">".$row['debtorno']."</td>";
		  echo "<td class=\"visible\">".$row['name']."</td>";
		  echo "<td class=\"visible\">".$row['student_total']."</td>";
		  echo "<td class=\"visible\">".$row['paid']."</td>";
		  echo "<td class=\"visible\">".$row['owing']."</td>";
		  echo "<td class=\"visible\">".$percent."%"."</td>";
		  $balance=$balance+$row['owing'];
		    echo "</tr>";
		  $j++;
			} ?>
<tr><td class="visible"><font size="4" color="maroon"> Page Balance </td><td class="visible"> -</td><td class="visible"> -</td><td class="visible"> -</td><td class="visible"><?php echo number_format($balance,2) ?> </font></td></tr>				
<tr><td class="visible"><font size="4" color="maroon"> All Students Balance </td><td class="visible"> -</td><td class="visible"> -</td><td class="visible"> -</td><td class="visible"><?php echo number_format($college_balance,2) ?> </font></td></tr>					
<?php			
if ($pageno == 1) {
   echo "<tr><td>"." FIRST PREV</td> ";
} else {
   echo "<td> <a href='{$_SERVER['PHP_SELF']}?pageno=1'>FIRST</a> ";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage'>PREV</a></td> ";
}
echo "<td> ( Page $pageno of $lastpage ) </td>";
if ($pageno == $lastpage) {
   echo "<td> NEXT LAST "."</td>";
} else {
   $nextpage = $pageno+1;
   echo "<td> <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage'>NEXT</a></td> ";
   echo "<td> <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>LAST</a></td></tr> ";
}		
?>
			</table><?php
include('includes/footer.inc');
?>
   
	