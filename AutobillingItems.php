<?php
ob_start();
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Manage Autobilling');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DefineCartClass.php'); ?>
<SCRIPT LANGUAGE="javascript">
$(document).ready(function(){	
  $('.columnQuantityClass,.columnPriceClass').change(function(){
  var totalPayableAmount=0;
	  var id=$(this).attr('id');
	  var index=id.substring(id.indexOf("_")+1);
	  document.getElementById("lineTotal_"+index).value = parseFloat(document.getElementById("price_"+index).value)*
	                                                    parseInt(document.getElementById("quantity_"+index).value);
	$(".lineTotalClass").each(function(){	
	 totalPayableAmount=parseInt(totalPayableAmount)+parseFloat($(this).val());	
	  })
	  document.getElementById("invoiceTotal").value=totalPayableAmount;
  })
  
  $("#invoiceTotal,#amountPaid").change(function(){
  document.getElementById("balance").value=parseFloat($("#invoiceTotal").val()) - parseFloat($("#amountPaid").val());
  
  })
  
$(function(){
	$("#product").keyup(function() 
	{ 
		var searchid = $(this).val();
		var dataString = 'search='+ searchid;
		if(searchid!='')
		{
			$.ajax({
			type: "POST",
			url: "search.php",
			data: dataString,
			cache: false,
			success: function(html)
			{
			
			}
			});
		}
		return false;    
	});
});
})
</script><?php
$msg='';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';	
echo '<tr><td>' . _('Class') . ":</td>
		<td><select name='class_id'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT cl.id,cl.class_name,gl.grade_level FROM classes cl 
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		ORDER BY class_name";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr>';
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
		echo '</select></td></tr></table>';
		echo '<table class="enclosed">';
echo "<br><div class='centre'><input  type='Submit' name='register' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";		
		
if (isset($_POST['register'])) {
$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period'];
		
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="content">';
echo '<table cellpading=10 class=enclosed style="margin-bottom:20px; margin-top:20px; -moz-border-radius:20px; border-radius:20px;">
<tr><td style="margin-left:55%;">Type Product Name</td><td><input type="text" name="product" id="product" size="80" placeholder="Search for products" /></td> 
<div id="result"></div>
</div>';
echo '<td><input type="submit" name="productSearch" value="Find Product" /></td></tr>';	
}

if (!isset($_SESSION['InvoiceItems'])){
	 $_SESSION['InvoiceItems'] = new Cart;
}
$NewItemQty = 1;
if(isset($_POST['productSearch'])){
$SearchString =$_POST['product'];
$sql="SELECT * FROM stockmaster where description LIKE '$SearchString'";
$ErrMsg = _('There is a problem selecting the part records to display because');
$SearchResult = DB_query($sql,$db,$ErrMsg);

if (DB_num_rows($SearchResult)==0)
{
	prnMsg(_('There are no products available that match the criteria specified'),'info');
	if ($debug==1)
	{
	  prnMsg(_('The SQL statement used was') . ':<br>' . $SQL,'info');
	}
}
if (DB_num_rows($SearchResult)==1)
{
	$myrow=DB_fetch_array($SearchResult);
	$_POST['NewItem'] = $myrow['stockid'];
	DB_data_seek($SearchResult,0);
	$newitem=$_POST['NewItem'];
}

  $sql = "SELECT stockmaster.actualcost,stockmaster.stockid,stockmaster.description,stockmaster.stockid,stockmaster.units,stockmaster.volume,
  stockmaster.kgs,
  (materialcost+labourcost+overheadcost) AS standardcost,stockmaster.mbflag,stockmaster.decimalplaces,stockmaster.controlled,stockmaster.serialised,
	stockmaster.discountcategory,stockmaster.taxcatid FROM stockmaster
	WHERE stockmaster.stockid = '". $_POST['NewItem'] . "'";
	$ErrMsg =  _('There is a problem selecting the part because');
	$result1 = DB_query($sql,$db,$ErrMsg);
	if ($myrow = DB_fetch_array($result1))
	{
	
		 $AlreadyOnThisCredit =0;

		   foreach ($_SESSION['InvoiceItems']->LineItems AS $OrderItem)
		    {
				$LineNumber = $_SESSION['InvoiceItems']->LineCounter;
			    if ($OrderItem->StockID ==$_POST['NewItem'])
				 {
				     $AlreadyOnThisCredit = 1;
					 //$NewItemQty =$NewItemQty+1;
				     prnMsg($_POST['NewItem'] . ' ' . _('is already on this invoice - the system will not allow the 
					 same item on the invoice more than once. However you can change the quantity for the existing line if 
					 necessary'),'warn');
			    }
		   } /* end of the foreach loop to look for preexisting items of the same code */
		   
		if ($AlreadyOnThisCredit!=1)
		{  			
			$_SESSION['InvoiceItems']->add_to_cart ($myrow['stockid'],$NewItemQty,$myrow['description'],$myrow['actualcost']
			,0,$myrow['units'],$myrow['volume'],$myrow['kgs'],0,$myrow['mbflag'],Date($_SESSION['DefaultDateFormat']),0,
			$myrow['discountcategory'],$myrow['controlled'],$myrow['serialised'],
			$myrow['decimalplaces'],'',	'No',-1,$myrow['taxcatid'],'','','',$myrow['standardcost']);
		}
							
	} 
	else 
	{
		prnMsg( $_POST['NewItem'] . ' ' . _('does not exist in the database and cannot therefore be added to the Invoice'),'warn');
	}

echo '</form>';
}//end of if(isset($_POST['productSearch']))
if (isset($_GET['Delete'])){
			$_SESSION['InvoiceItems']->remove_from_cart($_GET['Delete']);
		}
if (count($_SESSION['InvoiceItems']->LineItems)>0)
{
 echo '<form name="form1" action="'. $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
$LineNumber = $_SESSION['InvoiceItems']->LineCounter;

echo '<tr><td colspan=3><table class="whiteBorderedTD" border-spacing: 2px; cellpadding=2 style="margin-bottom:20px; -moz-border-radius:20px;
 border-radius:20px; width:100%;"></br>';
echo '<tr><td colspan="6" style="text-align:center;"><h1><b>'._('Invoice Items') . '</b></h3></td></tr>';
echo "<tr><th style='width:130%;'>" . _('Product Name') . "</th><th>" . _('unit Price') . "</th><th>" . _('Quantity') . "</th>
<th>" . _('Discount') ."</th><th>" . _('Amount') . "</th><th>" . _('Remove') . "</th></tr>";
		$k=0;
		$_SESSION['InvoiceItems']->total=0;
		foreach ($_SESSION['InvoiceItems']->LineItems as $InvoiceItem) 
		{			
			$LineTotal =  $InvoiceItem->Quantity * $InvoiceItem->Price * (1 - $InvoiceItem->DiscountPercent);
					
			$_SESSION['InvoiceItems']->total =$_SESSION['InvoiceItems']->total +$LineTotal;			
				echo '<input type="hidden" name="stockID[]" id="stock_'.$InvoiceItem->LineNumber.'" value="'.$InvoiceItem->StockID.'"/>';
				echo "<td>".$InvoiceItem->ItemDescription ."</td>";
				echo '<td><input type="text" class="columnPriceClass" name="Price[]" id="price_'.$InvoiceItem->LineNumber.'"
				 size=11 value="'.$InvoiceItem->Price.'"></td>';
				echo '<td><input type=TEXT class="columnQuantityClass" name="Quantity[]" id="quantity_'.$InvoiceItem->LineNumber.'" 
				maxlength=6 size=6 VALUE=' . $InvoiceItem->Quantity . '></td>';
               echo '<td><input type=TEXT class="columnDiscountClass" name="Discount[]" id="discount_'.$InvoiceItem->LineNumber.'" size=3 maxlength=3 
			   VALUE=' . ($LineItem->DiscountPercent * 100) . '>%</td>';				
			   echo '<td><input type="text" class="lineTotalClass" name="lineTotal[]" id="lineTotal_'.$InvoiceItem->LineNumber.'" 
			   value="'.$LineTotal.'"/></td>';
			   echo "<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $InvoiceItem->LineNumber . "'>" . _('Remove Product') . "</a></td></tr>";
			   
		}//end foreach ($_SESSION['InvoiceItems']->LineItems as $InvoiceItem)
		$_SESSION['form_already_loaded']=1;
		echo '<tr><td>Invoice Total</td><td><input type="text" name="invoiceTotal" id="invoiceTotal" 
		value="'.$_SESSION['InvoiceItems']->total.'" readonly=""/></td>';
		echo '<td><b>Enter Amount</b></td><td><input type="text" name="amountPaid" id="amountPaid" /></td>
		<td><b>Balance</b></td><td><input type="text" name="balance" id="balance" readonly="" /></td></tr>';
echo '<tr><td><input type="submit" name="submitInvoice" id="submitInvoice" value="Submit Invoice" /></td></tr></table>';
echo '</td></tr></table>';
if(isset($_POST['submitInvoice'])){
	if(isset($_POST['balance']) && isset($_POST['amountPaid']) && $_POST['balance']>-1)
	{  
		$PostingDate = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, Date('m'), 0,Date('Y')));
		$PeriodNo = GetPeriod($PostingDate,$db);
		
		$sql = "INSERT INTO salesorderdetails (debtor_id,transactiondate,addedby)
		VALUES (1,'" . date('Y-m-d H-i-s') . "','" . trim($_SESSION['UserID']) . "')";
		$DbgMsg = _('The SQL that failed was');
		$ErrMsg = _('Unable to add the quotation line');
		$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$sql="SELECT LAST_INSERT_ID()";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$lastInvoiceID = $myrow[0];
		
		$i=0;
		foreach($_POST['stockID'] as $lineItem)
		{
			$sql="INSERT INTO invoice_items(invoice_id,product_id,quantity,unit_price,discount) values('$lastInvoiceID','".$_POST['stockID'][$i]."',
			'".$_POST['Quantity'][$i]."','".$_POST['Price'][$i]."','".$_POST['Discount'][$i]."')";
			$DbgMsg = _('The SQL that failed was');
			$ErrMsg = _('Unable to add the invoice Item');
			$row = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	
			
			$sql="UPDATE stockmaster SET quantity=quantity-'".$_POST['Quantity'][$i]."' WHERE stockid='".$_POST['stockID'][$i]."'";
			$result=DB_query($sql,$db);
			
			
			$i++;
		}//end of foreach($_POST['stockID'] as $lineItem)
		$sql="INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount,posted,tag) 
		values(26,1,'" . date('Y-m-d H-i-s') . "','".$PeriodNo."',1020,'".$_POST['invoiceTotal']."',1,0)";
		$DbgMsg = _('The SQL that failed was');
		$ErrMsg = _('Unable to add the invoice Item');
		$row = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		$sql="INSERT INTO gltrans(type,typeno,trandate,periodno,account,amount,posted,tag) 
		values(26,1,'" . date('Y-m-d H-i-s') . "','".$PeriodNo."',1,-'".$_POST['invoiceTotal']."',1,0)";
		$DbgMsg = _('The SQL that failed was');
		$ErrMsg = _('Unable to add the invoice Item');
		$row = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		//$_SESSION['InvoiceItems'] = new Cart;
		unset($_SESSION['form_already_loaded']);
		unset($_SESSION['invoiceItems']);
		unset($_SESSION['InvoiceItems']->LineItems);
		
	}//end of if(!empty($_POST['balance']) && !empty($_POST['amountPaid']) && $_POST['balance'] > 0)
	else
	{
		prnMsg( _('Counter check the invoice to ensure amount paid and balance are well entered'),'warn');
	}
	}//end of if(isset($_POST['submitInvoice'])){
}//end of if (count($_SESSION['InvoiceItems']->LineItems)>0)

echo '</form>';
include('includes/footer.inc');
?>
