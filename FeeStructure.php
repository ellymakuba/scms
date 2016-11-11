<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
$PageSecurity = 3;
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc'); 
$title = _('Fee Structure');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');?>
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
  $('.columnPriceClass').change(function(){
        var totalPayableAmount=0;
	$(".columnPriceClass").each(function(){	
	 totalPayableAmount=parseInt(totalPayableAmount)+parseFloat($(this).val());	
	  })
	  document.getElementById("invoiceTotal").value=totalPayableAmount;
  })
  
  $("#invoiceTotal,#amountPaid").change(function(){
  document.getElementById("balance").value=parseFloat($("#invoiceTotal").val()) - parseFloat($("#amountPaid").val());
  
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
  	alert("am the value you are looking for "+$(this).val());	
      }); 
})
</script><?php
echo '<p class="page_title_text">' . ' ' . _('Create Fee Struture') . '';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="enclosed">';
echo '<tr><td>' . _('Academic Year') . ":</td>
<td><select name='year'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Year');
$sql="SELECT * FROM years";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) 
{
   echo '<option value='. $myrow['id'].  '>'.' '.$myrow['year'];
} //end while loop
echo '</select></td></tr>';	
echo '<tr><td>' . _('Class') . ":</td>
<td><select name='class_id'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
$sql="SELECT * FROM gradelevels 
ORDER BY grade_level";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
echo '<option value='. $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop
echo '</select></td></tr>';

echo '</table>';
echo '<table class="enclosed">';
if(!isset($_SESSION['classSession']) && !isset($_SESSION['year']))
{
echo "<div class='centre'><input  type='Submit' name='register' value='" . _('Create Fee Structure') . "'></div>";
}
echo "</form>";	
if (isset($_POST['register'])) 
{
        $feeStructureVariableErrors=0;
	if($_POST['class_id'] ==0){
	$feeStructureVariableErrors=1;
	       prnMsg( _('Please select class you want to create fee structure'),'error');
	}
	else if($_POST['year'] ==0){
	$feeStructureVariableErrors=1;
		prnMsg( _('Please select year you want to create fee structure'),'error');
	}
	if($feeStructureVariableErrors==0)
	{
	 $_SESSION['classSession'] = $_POST['class_id'];
	 $_SESSION['year'] = $_POST['year'];
	}	
	
	
}
if(isset($_GET['feeStructure']))
{
	$sql="SELECT * FROM autobilling WHERE id='".$_GET['feeStructure']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_array($result);
	$_SESSION['classSession'] = $myrow['class_id'];
	$_SESSION['year'] = $myrow['year_id'];
}	
if((isset($_SESSION['classSession']) && isset($_SESSION['year'])))
{
        echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
 	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
 	echo '<div class="content">';
 	echo '<table cellpading=10 class=enclosed style="margin-bottom:20px; margin-top:20px; -moz-border-radius:20px; border-radius:20px;">
 	<tr><td style="margin-left:55%;">Type Item Name</td><td><input type="text" name="product" id="product" size="80" placeholder="Type the first three characters to display item" /></td> 
 	<div id="result"></div>
 	</div>';
 	echo '<td><input type="submit" name="productSearch" value="'._('Add Item').'" /></td></tr>';
}
if (!isset($_SESSION['InvoiceItems'])){
	 $_SESSION['InvoiceItems'] = new Cart;
}
$NewItemQty = 1;
if(isset($_GET['feeStructure']))
{	
	$_SESSION['InvoiceItems'] = new Cart;
	$sql="SELECT * FROM autobilling_items
	WHERE autobilling_id='" . $_GET['feeStructure'] . "'
	ORDER BY id";
	$result=DB_query($sql,$db);
	while($rows=DB_fetch_array($result))
	{
		$sql2="SELECT stockmaster.unit_price,stockmaster.id,stockmaster.description  FROM stockmaster
	       WHERE stockmaster.id='".$rows['product_id']."'";
		$result2=DB_query($sql2,$db);
		$myrow2 = DB_fetch_array($result2);
		
		$_SESSION['InvoiceItems']->add_to_cart ($myrow2['id'],$NewItemQty,$myrow2['description'],$rows['amount']
		,0,1,1,1,0,1,Date($_SESSION['DefaultDateFormat']),0,1,1,1,1,'','No',-1,1,'','','',1);
	}	
	$_SESSION['feeStructure']=$_GET['feeStructure'];
}
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
	$_POST['NewItem'] = $myrow['id'];
	DB_data_seek($SearchResult,0);
	$newitem=$_POST['NewItem'];
}

  $sql = "SELECT stockmaster.unit_price,stockmaster.id,stockmaster.description,stockmaster.id FROM stockmaster
	WHERE stockmaster.id = '". $_POST['NewItem'] . "'";
	$ErrMsg =  _('There is a problem selecting the part because');
	$result1 = DB_query($sql,$db,$ErrMsg);
	if ($myrow = DB_fetch_array($result1))
	{	
		 $AlreadyOnThisCredit =0;
		   foreach ($_SESSION['InvoiceItems']->LineItems AS $OrderItem)		    {
				$LineNumber = $_SESSION['InvoiceItems']->LineCounter;
			    if ($OrderItem->StockID ==$_POST['NewItem'])
				 {
				     $AlreadyOnThisCredit = 1;
					 //$NewItemQty =$NewItemQty+1;
				     prnMsg($_POST['NewItem'] . ' ' . _('is already on this Fee Structure - the system will not allow the 
					 same item  more than once.'),'warn');
			    }
		   } /* end of the foreach loop to look for preexisting items of the same code */		   
		if ($AlreadyOnThisCredit!=1)
		{  			
			$_SESSION['InvoiceItems']->add_to_cart ($myrow['id'],$NewItemQty,$myrow['description'],$myrow['unit_price']
			,0,1,1,1,0,1,Date($_SESSION['DefaultDateFormat']),0,1,1,1,1,'','No',-1,1,'','','',1);
		}
							
	} 
	else 
	{
		prnMsg( $_POST['NewItem'] . ' ' . _('does not exist in the database and cannot therefore be added to the Invoice'),'warn');
	}

echo '</form>';
}//end of if(isset($_POST['productSearch']))
if (isset($_GET['Delete']))
{
	$_SESSION['InvoiceItems']->remove_from_cart($_GET['Delete']);
}
if (isset($_GET['Update']))
{
	$_SESSION['InvoiceItems']->update_cart_item($_GET['Update'],'2','500','10','','No','0','14','5');
}
if (count($_SESSION['InvoiceItems']->LineItems)>0)
{
	 echo '<form name="form1" action="'. $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$LineNumber = $_SESSION['InvoiceItems']->LineCounter;
	$sql = 'SELECT grade_level FROM gradelevels WHERE id="'.$_SESSION['classSession'].'"';
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_array($result);
	$class_session=$myrow['grade_level'];
	
	$LineNumber = $_SESSION['InvoiceItems']->LineCounter;
	$sql = 'SELECT * FROM years WHERE id="'.$_SESSION['year'].'"';
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_array($result);
	$year_session=$myrow['year'];

echo '<tr><td colspan=3><table class="whiteBorderedTD" border-spacing: 2px; cellpadding=2 style="margin-bottom:20px; -moz-border-radius:20px;
 border-radius:20px; width:100%;"></br>';
echo '<tr><td colspan="6" style="text-align:center;"><h1><b>'.$class_session.' '.$year_session.' '._('Fee Structure Items') . '</b></h3></td></tr>';
echo "<tr><th style='width:40%;'>" . _('Item Name') . "</th><th>" . _('Amount') . "</th><th>" . _('Priority') . "</th><th>" . _('Remove') . "</th></tr>";
		$k=0;
		$_SESSION['InvoiceItems']->total=0;
		foreach ($_SESSION['InvoiceItems']->LineItems as $InvoiceItem) 
		{			
			$LineTotal =  $InvoiceItem->Quantity * $InvoiceItem->Price * (1 - $InvoiceItem->DiscountPercent);
			echo '<tr>';		
			$_SESSION['InvoiceItems']->total =$_SESSION['InvoiceItems']->total +$LineTotal;	
			echo '<input type="hidden" name="id[]" id="stock_'.$InvoiceItem->LineNumber.'" value="'.$InvoiceItem->StockID.'" />';
			echo "<td>".$InvoiceItem->ItemDescription ."</td>";
			echo '<td><input type="text" class="columnPriceClass" name="Price[]" id="price_'.$InvoiceItem->LineNumber.'"  value="'.$InvoiceItem->Price.'"></td>';
			echo '<td><input type="text"  class="columnPriorityClass" name="priority[]" id="priority_'.$InvoiceItem->LineNumber.'" size=5 value="'.$InvoiceItem->LineNumber.'"></td>';
			echo "<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $InvoiceItem->LineNumber . "'>" . _('Remove Product') . "</a></td></tr>";
			   
		}//end foreach ($_SESSION['InvoiceItems']->LineItems as $InvoiceItem)
		$_SESSION['form_already_loaded']=1;
		echo '<tr><td>Fee Structure Total</td><td><input type="text" name="invoiceTotal" id="invoiceTotal" 
		value="'.number_format($_SESSION['InvoiceItems']->total,2).'" readonly=""/></td></tr>';
echo '<td><input type="submit" name="submitInvoice" id="submitInvoice" value="Save Fee Structure" /></td>';
echo "</td><td><td><input  type=submit name='cancelInvoice' VALUE='" . _('Cancel Fee Structure') . "' onclick=\"return confirm('" . _('Are you sure you want to cancel this Fee Structure') . "');\"></td></tr>";
echo '</table>';
if(isset($_POST['cancelInvoice'])){
		unset($_SESSION['form_already_loaded']);
		unset($_SESSION['invoiceItems']);
		unset($_SESSION['feeStructure']);
		unset($_SESSION['InvoiceItems']->LineItems);
		unset($_SESSION['InvoiceItems']->LineCounter);
		unset($_SESSION['year']);
		unset($_SESSION['classSession']);
		$_SESSION['InvoiceItems'] = new Cart;
		 echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath ."/FeeStructure.php". "'>";
}
if(isset($_POST['submitInvoice'])){
		$PostingDate = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, Date('m'), 0,Date('Y')));
		$PeriodNo = GetPeriod($PostingDate,$db);
		
		if(isset($_SESSION['feeStructure']))
		{
			$sql="DELETE FROM autobilling_items WHERE autobilling_id='".$_SESSION['feeStructure']."'";
			$result = DB_query($sql,$db);
			
			$i=0;	
			foreach($_POST['id'] as $value)
			{			
				$sql = "SELECT id FROM autobilling_items
				WHERE autobilling_id='". $lastID ."'
				AND product_id='". $_POST['stockID'.$value] ."'";
				$result=DB_query($sql,$db);
				if(DB_fetch_row($result)>0)
				{
					prnMsg(_($value._(' ').'has already been invoiced for this year'),'warn');	
				}
				else
				{
					$sql = "INSERT INTO autobilling_items (autobilling_id,product_id,amount,priority) 
					VALUES ('" .$_SESSION['feeStructure'] ."','" .$_POST['id'][$i] ."','" .$_POST['Price'][$i] ."','" .$_POST['priority'][$i] ."') ";
					$result=DB_query($sql,$db);
				
				}
				$i++;
			    }
		}//end of if(isset($_SESSION['feeStructure']))
		else
		{
		$sql = "SELECT id FROM autobilling WHERE class_id='". $_SESSION['classSession'] ."'
		AND year_id='". $_SESSION['year'] ."'";
		$result=DB_query($sql,$db);
		if(DB_fetch_row($result)>0){
		prnMsg(_('The Fee Structure for this class has already been created'),'warn');
		exit();	
		}
		
		$sql = "INSERT INTO autobilling (class_id,year_id) 
		VALUES ('" .$_SESSION['classSession'] ."','" .$_SESSION['year'] ."') ";
		$ErrMsg = _('The fee structure items not updated because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg( _('items added successfully'),'success');
		
		$sql="SELECT LAST_INSERT_ID()";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$lastID = $myrow[0];
	$i=0;	
	foreach($_POST['id'] as $value)
	{			
		$sql = "SELECT id FROM autobilling_items
		WHERE autobilling_id='". $lastID ."'
		AND product_id='". $_POST['stockID'.$value] ."'";
		$result=DB_query($sql,$db);
		if(DB_fetch_row($result)>0)
		{
			prnMsg(_($value._(' ').'has already been invoiced for this year'),'warn');	
		}
		else
		{
			$sql = "INSERT INTO autobilling_items (autobilling_id,product_id,amount,priority) 
			VALUES ('" .$lastID ."','" .$_POST['id'][$i] ."','" .$_POST['Price'][$i] ."','" .$_POST['priority'][$i] ."') ";
			$result=DB_query($sql,$db);
		
		}
		$i++;
	    }
	    }
	 	prnMsg( _('products added successfully'),'success');
	    	unset($_SESSION['form_already_loaded']);
	    	unset($_SESSION['invoiceItems']);
	    	unset($_SESSION['feeStructure']);
	    	unset($_SESSION['InvoiceItems']->LineItems);
	    	unset($_SESSION['InvoiceItems']->LineCounter);
	    	unset($_SESSION['year']);
	    	unset($_SESSION['classSession']);
	    	$_SESSION['InvoiceItems'] = new Cart;
	    	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath ."/FeeStructure.php". "'>";   	
	}//end of if(isset($_POST['submitInvoice'])){
}//end of if (count($_SESSION['InvoiceItems']->LineItems)>0)

echo '</form>';
include('includes/footer.inc');
?>
