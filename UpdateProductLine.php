<?php
include('includes/session.inc');
include('includes/DefineCartClass.php');
$_SESSION['InvoiceItems']->update_cart_item($_GET['lineItem'],'2','500','10','','No','0','14','5');
?>
