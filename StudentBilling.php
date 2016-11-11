<?php
$PageSecurity = 5;
include('includes/DefineCartClass.php');
include('includes/session.inc');
$title = _('Fee Structure');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
?>
<SCRIPT LANGUAGE="javascript">
$(document).ready(function(){
 $("#product").autocomplete({
 source:function(request,response){
     $.getJSON("search.php?term="+request.term,function(result){
     response($.map(result,function(item){
         return{
        id:item.stockid,
        value:item.description
        }
     }))
    })
 },
 minLength:3,
  messages: {
        noResults: '',
        results: function() {}
    }
  });
    $('.columnQuantityClass,.columnPriceClass,.columnDiscountClass').change(function(){
  var totalPayableAmount=0;
	  var id=$(this).attr('id');
	  var index=id.substring(id.indexOf("_")+1);
	  var discount=parseInt($("#discount_"+index).val());
	  if(isNaN(discount)){
	      document.getElementById("discount_"+index).value=0;
	  }
	  document.getElementById("lineTotal_"+index).value = parseFloat(document.getElementById("price_"+index).value)-parseInt(document.getElementById("discount_"+index).value);
	$(".lineTotalClass").each(function(){
	 totalPayableAmount=parseInt(totalPayableAmount)+parseFloat($(this).val());
	  })
	  document.getElementById("invoiceTotal").value=totalPayableAmount;
  })

  $(".columnPriorityClass").change(function(){
       $.ajax({
          type:"POST",
          url:"ManageFeeStructures.php",
          async:false,
          success:function(){
            alert("am the value you are looking for "+$(this).val())
          }
      });
      });
})
</script><?php
if(isset($_REQUEST['student_id']))
{
	$_SESSION['student'] = $_REQUEST['student_id'];
	$sql = "SELECT gl.id FROM debtorsmaster dm INNER JOIN classes cl ON dm.class_id=cl.id
	INNER JOIN gradelevels gl ON cl.grade_level_id=gl.id
	WHERE dm.id='" . $_SESSION['student'] . "'";
	$result= DB_query($sql, $db);
	$myrow= DB_fetch_array($result);
	$_SESSION['studentClassSession'] = $myrow['id'];
}
if(isset($_REQUEST['invoice_id']))
{
	$_SESSION['invoice_id']=$_REQUEST['invoice_id'];
	$_SESSION['period']=$_REQUEST['period_id'];
}
echo '<p class="page_title_text">' . ' ' . _('Student Billing Form') . '';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="enclosed">';
echo '<tr><td>' . _('Student') . ":</td>";
$sql= "SELECT * FROM debtorsmaster WHERE id='" . $_SESSION['student'] . "'";
$result= DB_query($sql, $db);
$myrow = DB_fetch_array($result);
echo '<td>' . $myrow['debtorno'] . ' ' . $myrow['name'] . '</td></tr>';
echo '<tr><td>' . _('Academic Year') . ":</td><td><select name='year'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Year');
$sql    = "SELECT * FROM years";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result))
{
   echo '<option value='. $myrow['id'].  '>'.' '.$myrow['year'];
} //end while loop
echo '</select></td></tr>';
echo '</table>';
echo '<table class="enclosed">';
    echo "<div class='centre'><input  type='Submit' name='loadFeeStructure' value='" . _('Load Fee Structure') . "'></div>";
echo "</form>";

if (isset($_POST['loadFeeStructure'])) {
    $feeStructureVariableErrors = 0;
    if ($_POST['year'] == 0) {
        $feeStructureVariableErrors = 1;
        prnMsg(_('Please select year you want to create fee structure'), 'error');
    }
    if($feeStructureVariableErrors == 0) {
        $_SESSION['year'] = $_POST['year'];
    }
    $sql = "SELECT ab.* FROM autobilling ab
    WHERE  ab.class_id='".$_SESSION['studentClassSession']."'
    AND ab.year_id='".$_POST['year']."'";
    $result = DB_query($sql,$db);
    $num_rows = DB_num_rows($result);
    if ($num_rows<0 || $num_rows==0)
    {
    	prnMsg(_($_SESSION['studentClassSession'].' The fee structure for this class has not been created for this terms.'),'warn');
      exit();
    }
}

if (!isset($_SESSION['classFeeStructure'])) {
    $_SESSION['classFeeStructure'] = new Cart;
}
$NewItemQty = 1;
if ($_SESSION['studentClassSession'] > 0 && isset($_SESSION['period']) && !isset($_SESSION['invoice_id'])){
    $_SESSION['classFeeStructure'] = new Cart;
    $sql = "SELECT id FROM autobilling WHERE year_id='" . $_SESSION['year'] . "' AND class_id='" . $_SESSION['studentClassSession'] . "'";
    $result= DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_SESSION['feeStructure']= $myrow['id'];

    $sql    = "SELECT * FROM autobilling_items
    WHERE autobilling_id='" . $_SESSION['feeStructure'] . "'
    ORDER BY id";
    $result = DB_query($sql, $db);
    while ($rows = DB_fetch_array($result)) {
    $sql2    = "SELECT stockmaster.unit_price,stockmaster.id,stockmaster.description  FROM stockmaster
    WHERE stockmaster.id='" . $rows['product_id'] . "'";
    $result2 = DB_query($sql2, $db);
    $myrow2  = DB_fetch_array($result2);
    $_SESSION['classFeeStructure']->add_to_cart($myrow2['id'], $NewItemQty, $myrow2['description'], $rows['amount'], 0, 1, 1, 1, 0, 1, Date($_SESSION['DefaultDateFormat']), 0, 1, 1, 1, 1, '', 'No', -1, 1, '', '', '', 1);
    }
}
if ($_SESSION['studentClassSession'] > 0 && isset($_SESSION['period']) && isset($_SESSION['invoice_id'])){
    $_SESSION['classFeeStructure'] = new Cart;
    $sql= "SELECT * FROM invoice_items
    WHERE invoice_id='" . $_SESSION['invoice_id'] . "'
    ORDER BY id";
    $result = DB_query($sql, $db);
    while ($rows = DB_fetch_array($result))
	{
        $sql2    = "SELECT stockmaster.unit_price,stockmaster.id,stockmaster.description  FROM stockmaster
         WHERE stockmaster.id='" . $rows['product_id'] . "'";
        $result2 = DB_query($sql2, $db);
        $myrow2  = DB_fetch_array($result2);
        $_SESSION['classFeeStructure']->add_to_cart($myrow2['id'], $NewItemQty, $myrow2['description'], $rows['unitprice'],$rows['discount'], 1, 1, 1, 0, 1, Date($_SESSION['DefaultDateFormat']), 0, 1, 1, 1, 1, '', 'No', -1, 1, '', '', '', 1);
    }
}
echo '</form>';
if (isset($_GET['Delete'])) {
    $_SESSION['classFeeStructure']->remove_from_cart($_GET['Delete']);
}
if (isset($_GET['Update'])) {
    $_SESSION['classFeeStructure']->update_cart_item($_GET['Update'], '2', '500', '10', '', 'No', '0', '14', '5');
}
if (count($_SESSION['classFeeStructure']->LineItems) > 0) {
    echo '<form name="form1" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    $LineNumber    = $_SESSION['InvoiceItems']->LineCounter;
    $sql           = 'SELECT class_name FROM classes WHERE id="' . $_SESSION['studentClassSession'] . '"';
    $result        = DB_query($sql, $db);
    $myrow         = DB_fetch_array($result);
    $class_session = $myrow['class_name'];

    $LineNumber = $_SESSION['classFeeStructure']->LineCounter;
    $sql= 'SELECT cp.id,terms.title,years.year FROM collegeperiods cp
    INNER JOIN terms ON terms.id=cp.term_id
    INNER JOIN years ON years.id=cp.year WHERE cp.id="' . $_SESSION['period'] . '"';
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $period_session = $myrow['title'] . ' ' . $myrow['year'];

	$sql = "SELECT * FROM debtorsmaster WHERE id='" . $_SESSION['student'] . "'";
	$result= DB_query($sql, $db);
	$myrow= DB_fetch_array($result);
	$studentDetails= $myrow['debtorno'] . '-' . $myrow['name'];

    echo '<tr><td colspan=3><table class="whiteBorderedTD" border-spacing: 2px; cellpadding=2 style="margin-bottom:20px; -moz-border-radius:20px;
 border-radius:20px; width:100%;"></br>';
    echo '<tr><td colspan="6" style="text-align:center;"><h1><b>' .$studentDetails . '</b></h1></td></tr>';
	echo '<tr><td colspan="6" style="text-align:center;"><h3><b>' . $class_session . ' ' . $period_session . ' ' . _('Fee Structure Items') . '</b></h3></td></tr>';
    echo "<tr><th style='width:40%;'>" . _('Item Name') . "</th>";
	echo "<th>" . _('Amount') . "</th>";
	echo "<th>" . _('Discount') . "</th>";
	echo "<th>" . _('LineTotal') . "</th>";
	echo "<th>" . _('Priority') . "</th>";
	if(!isset($_SESSION['invoice_id'])) {
	echo "<th>" . _('Remove') . "</th>";
	}
	echo "</tr>";
    $k= 0;
    $_SESSION['classFeeStructure']->total = 0;
    foreach ($_SESSION['classFeeStructure']->LineItems as $InvoiceItem) {
        $LineTotal = $InvoiceItem->Price  - $InvoiceItem->DiscountPercent;
        echo '<tr>';
        $_SESSION['classFeeStructure']->total = $_SESSION['classFeeStructure']->total + $LineTotal;
        echo '<input type="hidden" name="id[]" id="stock_' . $InvoiceItem->LineNumber . '" value="' . $InvoiceItem->StockID . '" />';
        echo "<td>" . $InvoiceItem->ItemDescription . "</td>";
        echo '<td><input type="text" class="columnPriceClass" name="Price[]" id="price_' . $InvoiceItem->LineNumber . '"  value="' . $InvoiceItem->Price . '" readonly></td>';
		echo '<td><input type="text"  class="columnDiscountClass" name="discount[]" id="discount_' . $InvoiceItem->LineNumber . '" size=5 value="' . $InvoiceItem->DiscountPercent . '"></td>';
	  echo '<td><input type="text" class="lineTotalClass" name="lineTotal[]" id="lineTotal_'.$InvoiceItem->LineNumber.'"
			 value="'.$LineTotal.'" size=5"/></td>';
    echo '<td><input type="text"  class="columnPriorityClass" name="priority[]" id="priority_' . $InvoiceItem->LineNumber . '" size=5 value="' . $InvoiceItem->LineNumber . '"></td>';
	 if(!isset($_SESSION['invoice_id']))
		{
        echo "<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $InvoiceItem->LineNumber . "'>" . _('Remove Product') . "</a></td></tr>";
      }
    } //end foreach ($_SESSION['InvoiceItems']->LineItems as $InvoiceItem)
    $_SESSION['form_already_loaded'] = 1;
    echo '<tr><td>Fee Structure Total</td><td><input type="text" name="invoiceTotal" id="invoiceTotal"
     value="' . number_format($_SESSION['classFeeStructure']->total, 2) . '" readonly=""/></td></tr>';
	if(!isset($_SESSION['invoice_id'])){
	 echo '<td><input type="submit" name="submitInvoice" id="submitInvoice" value="Invoice Student" /></tr>';
	}
   else{
    echo '<td><input type="submit" name="updateInvoice" id="updateInvoice" value="Update Student Invoice" /></tr>';
   }
    echo '</table>';
    if (isset($_POST['submitInvoice'])) {
        $PostingDate = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 0, Date('Y')));
        $PeriodNo    = GetPeriod($PostingDate, $db);

        $sql_exist    = "SELECT id FROM salesorderdetails
        WHERE period_id='" . $_SESSION['period'] . "'
        AND student_id='" . $_SESSION['student'] . "'";
        $result_exist = DB_query($sql_exist, $db);
        if (DB_fetch_row($result_exist) > 0) {
            prnMsg(_(' This student has already been invoiced for this term'), 'warn');
        } else {

            $sql = "INSERT INTO salesorderdetails(student_id,invoice_date,transactiondate,addedby,period_id)
           VALUES ('" . $_SESSION['student'] . "','" . date('Y-m-d') . "','" . date('Y-m-d') . "','" . trim($_SESSION['UserID']) . "',
		   '" . $_SESSION['period'] . "')";
            $DbgMsg = _('The SQL that failed was');
            $ErrMsg= _('Unable to add the quotation line');
            $Ins_LineItemResult = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
            $sql  = "SELECT LAST_INSERT_ID()";
            $result= DB_query($sql, $db);
            $myrow= DB_fetch_row($result);
            $lastID= $myrow[0];

            $glquery  = "SELECT SUM(amount) as total FROM autobilling_items
             WHERE autobilling_id='" . $_SESSION['feeStructure'] . "'";
            $glresult = DB_query($glquery, $db);
            $glmyrow  = DB_fetch_array($glresult);
            $glamount = $glmyrow['total'];

            $query  = "INSERT INTO gltrans ( type,typeno,trandate,periodno,account,amount)
            VALUES (10,'" . $id . "','" . date('Y-m-d H-i-s') . "','" . $PeriodNo . "',1100,'" . $glamount . "')";
            $result = DB_query($query, $db);

            $query  = "INSERT INTO gltrans ( type,typeno,trandate,periodno,account,amount)
            VALUES (10,'" . $id . "','" . date('Y-m-d H-i-s') . "','" . $PeriodNo . "',1,'" . -$glamount . "')";
            $result = DB_query($query, $db);

            $i = 0;
            foreach ($_POST['id'] as $value) {
                $sql    = "INSERT INTO invoice_items ( invoice_id,product_id,amount,priority)
                VALUES ('" . $lastID . "','" . $_POST['id'][$i] . "','" . $_POST['Price'][$i] . "','" . $_POST['priority'][$i] . "') ";
                $result = DB_query($sql, $db);
                $i++;
            }

        }
        prnMsg(_('Invoicing successfully'), 'success');
        unset($_SESSION['form_already_loaded']);
        unset($_SESSION['classFeeStructure']);
        unset($_SESSION['feeStructure']);
        unset($_SESSION['classFeeStructure']->LineItems);
        unset($_SESSION['classFeeStructure']->LineCounter);
        unset($_SESSION['period']);
        unset($_SESSION['studentClassSession']);
		unset($_SESSION['invoice_id']);
        $_SESSION['classFeeStructure'] = new Cart;
        echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/selectStudent.php" . "'>";
    } //end of if(isset($_POST['submitInvoice'])){

	//begin updating invoice
	 if (isset($_POST['updateInvoice'])){
        $PostingDate = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 0, Date('Y')));
        $PeriodNo    = GetPeriod($PostingDate, $db);

		   $sql="DELETE FROM invoice_items WHERE invoice_id='".$_SESSION['invoice_id']."'";
		   $result = DB_query($sql,$db);

            $i = 0;
            foreach ($_POST['id'] as $value)
			{
               $sql    = "INSERT INTO invoice_items ( invoice_id,product_id,unitprice,discount,priority)
              VALUES ('" . $_SESSION['invoice_id'] . "','" . $_POST['id'][$i] . "','" . $_POST['Price'][$i] . "','" . $_POST['discount'][$i] . "',
			  '" . $_POST['priority'][$i] . "') ";
               $result = DB_query($sql, $db);
               $i++;
            }

        prnMsg(_('Invoice successfully updated'), 'success');
        unset($_SESSION['form_already_loaded']);
        unset($_SESSION['classFeeStructure']);
        unset($_SESSION['feeStructure']);
        unset($_SESSION['classFeeStructure']->LineItems);
        unset($_SESSION['classFeeStructure']->LineCounter);
        unset($_SESSION['period']);
        unset($_SESSION['studentClassSession']);
		unset($_SESSION['invoice_id']);
        $_SESSION['classFeeStructure'] = new Cart;
        echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/selectStudent.php" . "'>";
    } //end of if(isset($_POST['update'])){
} //end of if (count($_SESSION['InvoiceItems']->LineItems)>0)

echo '</form>';
include('includes/footer.inc');
?>
