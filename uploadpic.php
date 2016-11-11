
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Upload Pic</title>
<link rel="stylesheet" type="text/css" href="css/main.css"/>
<style type="text/css">
<!--
.style1 {color: #FFFFFF}
-->
</style>
</head>

<body>
<?php
 
$PageSecurity = 3;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');	
  
if (isset($_REQUEST["studentid"]))
  $id=$_REQUEST["studentid"];
    
if ((isset($_REQUEST['form_submit'])) && ('form_uploader' == $_REQUEST['form_submit']) && (!empty($_POST["imgnum"])))
{
  if  (is_uploaded_file($_FILES['file']['tmp_name']))
  {
    $filename = $_FILES['file']['name'];
    $filetype=$_FILES['file']['type'];
    $file_temp=$_FILES['file']['tmp_name'];	
  } 
  
 
   
  $fd = fopen($file_temp, "rb");
  $filesize=filesize($file_temp);
  $data=addslashes(fread($fd,$filesize));
  

 
  fclose($fd);
  $id=$_POST["imgnum"];
  
   
   if (!empty($id))
   {
     echo $filesize;
	 echo $id;
	 
     $sql= "update debtorsmaster
     set
	 photo='$data'
	 where id=$id";
	 
    $ErrMsg = _('This student could not be added because');
	$result = DB_query($sql,$db,$ErrMsg);

    
     print("Photo Successfully uploaded.<a href=javascript:window.close()>Click Here to Close the Window.</a>");
     print("</strong>");
   }
 } 
  ?>
<form name="uploadpicfrm" enctype="multipart/form-data" method="post" action="uploadpic.php"> 
<?php
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
 echo '<table class=enclosed>';
?>

  <tr>
    <th scope="col">Browse to The Picture You wish to Upload then Click on Save to Post. </th>
  </tr>
  <tr>
    <td scope="col">
      <input type="file" name="file"></td>
    </tr>
  <tr>
    <td scope="col"><div align="right">
      <input type="submit" name="Submit" value="Submit">
    </div></td>
    </tr>
</table>
 <input name="form_submit" type="hidden" id="form_submit" value="form_uploader">
 <input type="hidden" name="imgnum" <?php echo "value=$id"?> >
</form>
<p>&nbsp;</p>
<?php include('includes/footer.inc'); ?>
</body>
</html>



