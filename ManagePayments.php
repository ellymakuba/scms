<?php
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Manage Payments');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo '<table class="enclosed"><form name="managepayment" action="ManagePayments.php" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
?>
<tr><th>Action</th><th>Receipt No</th><th>Invoice</th><th>Student</th><th>Amount</th>
<th>Type</th><th>Date</th></tr>
<?php
   
  if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}

$sql = "SELECT count(*) FROM debtortrans";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "ManagePayment.php";
$rows_per_page = 25;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;			

$sql = "SELECT debtortrans.*,debtorsmaster.name as student_name
		FROM debtortrans,debtorsmaster,salesorderdetails
		WHERE debtortrans.transno = salesorderdetails.id 
		AND salesorderdetails.student_id = debtorsmaster.id
		ORDER BY debtortrans.id DESC $limit";

            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			while ($row = DB_fetch_array($result))
			{
			 if (($j%2)==1)
		    echo "<tr bgcolor=\"F0F0F0\">";
		  else
		    echo "<tr bgcolor=\"FFFFFF\">";
			$ovamount=-$row['ovamount']; ?>
			<td><?php 
			echo '<a target="_blank"  href="' . $rootpath . '/PDFReceipt.php?BatchNumber=' . $row['transno']. '&ReceiptNumber='.$row['id'].'">'._('Print Receipt').'</a>';
			?></td><?php
		  echo "<td>".$row['id']."</td>";
		  echo "<td>".$row['transno']."</td>";
		  echo "<td>".$row['student_name']."</td>";
		  echo "<td>".$ovamount."</td>";
		  echo "<td>".$row['method']."</td>";
		  echo "<td>".$row['trandate']."</td>";
		  
		    echo "</tr>";
		  $j++;
			}

if ($pageno == 1) {
   echo " FIRST PREV ";
} else {
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=1'>FIRST</a> ";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage'>PREV</a> ";
}
echo " ( Page $pageno of $lastpage ) ";
if ($pageno == $lastpage) {
   echo " NEXT LAST ";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage'>NEXT</a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>LAST</a> ";
}
echo "</table>";			
include('includes/footer.inc');
?>
