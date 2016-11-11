<?php
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Annual Class Billing');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text">' . ' ' .$title . '';
$msg='';
if ($_POST['Bill']==_('Bill Class'))
{
	if (!Is_Date($_SESSION['DateBanked']))
	{
		$_SESSION['DateBanked']= Date($_SESSION['DefaultDateFormat']);	 
	}
	$PeriodNo = GetPeriod($_SESSION['DateBanked'],$db);
	 
		$sql="SELECT id FROM classes WHERE grade_level_id='".$_SESSION['class']."'";
		$result=DB_query($sql,$db);
		while($myrow=DB_fetch_row($result))
		{
		     $streamsInClass[]=$myrow[0];
		}
		$streamsInClass=implode(', ', $streamsInClass);
			      
		$sql = "SELECT ab.* FROM autobilling ab
		INNER JOIN gradelevels gl ON gl.id=ab.class_id
		INNER JOIN classes cl ON cl.grade_level_id=gl.id
		INNER JOIN debtorsmaster dm ON dm.class_id=cl.id
		WHERE  ab.class_id='".$_SESSION['class']."'
		AND ab.year_id='".$_SESSION['year']."'";
		$result = DB_query($sql,$db);
		$num_rows = DB_num_rows($result);
		if ($num_rows<0 || $num_rows==0) 
		{
			prnMsg(_($_SESSION['class']._(' ').'The fee structure for this class has not been created for this year.'),'warn');
	        	exit();
	        }
		 else 
		 {	
		$sql = "SELECT ab.* FROM autobilling ab
		INNER JOIN gradelevels gl ON gl.id=ab.class_id
		INNER JOIN classes cl ON cl.grade_level_id=gl.id
		INNER JOIN debtorsmaster dm ON dm.class_id=cl.id
		WHERE  ab.class_id='".$_SESSION['class']."'
		AND ab.year_id='".$_SESSION['year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$auto_id=$myrow['id'];
		
		$sql2 = "SELECT * FROM debtorsmaster WHERE class_id IN ($streamsInClass)
		AND status=0";
		$result2 = DB_query($sql2,$db);
		$student_no=DB_num_rows($result2);
		if($student_no>0)
		{
		   while ($myrow2= DB_fetch_array($result2))
		   {
			$sql_exist = "SELECT id FROM salesorderdetails
		        WHERE year_id='". $_SESSION['year'] ."'
			AND student_id='". $myrow2['id'] ."'";
			$result_exist=DB_query($sql_exist,$db);
			if(DB_fetch_row($result_exist)>0)
			{
				prnMsg(_($myrow2['debtorno']._(' ').'has already been invoiced for this Year'),'warn');	
			}
			else
			{
				$students=$myrow2['id'];
				$sql = "INSERT INTO salesorderdetails(student_id,invoice_date,transactiondate,addedby,year_id)
				VALUES ('".$students."','".date('Y-m-d H-i-s')."','" . date('Y-m-d H-i-s'). "','" . trim($_SESSION['UserID']) . "','" . $_SESSION['year'] . "')";
				$DbgMsg = _('The SQL that failed was');
				$ErrMsg = _('Unable to add the quotation line');
				$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				$sql="SELECT LAST_INSERT_ID()";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				$id = $myrow[0];
			
				$glquery = "SELECT SUM(amount) as total FROM autobilling_items 
				WHERE autobilling_id='".$auto_id."'";
				$glresult = DB_query($glquery,$db);
				$glmyrow = DB_fetch_array($glresult);
				$glamount = $glmyrow['total'];
				
				$query = "INSERT INTO gltrans ( type,typeno,trandate,periodno,account,amount)
				VALUES (10,'".$id."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1100,'".$glamount."')";
				$result = DB_query($query,$db);
					
				$query = "INSERT INTO gltrans ( type,typeno,trandate,periodno,account,amount)
				VALUES (10,'".$id."','".date('Y-m-d H-i-s')."','" . $PeriodNo . "',1,'".-$glamount."')";
				$result = DB_query($query,$db);
			
				$sql3 = "SELECT * FROM autobilling_items 
				WHERE autobilling_id='".$auto_id."'";
				$result3 = DB_query($sql3,$db);		
			
				while($myrow3 = DB_fetch_array($result3))
				{	
					$sql = "INSERT INTO invoice_items ( invoice_id,product_id,amount,priority)
					VALUES ('".$id."','".$myrow3['product_id']."','".$myrow3['amount']."','".$myrow3['priority']."')";
					$DbgMsg = _('The SQL that failed was');
					$ErrMsg = _('Unable to add the quotation line');
					$Ins_LineItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	
				}			
						
			}
		}
	    }	
		
		$sql="SELECT grade_level FROM gradelevels WHERE id='".$_SESSION['class']."'";
		$result = DB_query($sql,$db);
		$myrow=DB_fetch_array($result);		
		prnMsg(_($myrow['grade_level']._(' ').'Class has been succesfully billed...'),'success');
		exit();
	}
}
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class=enclosed>';
echo '<tr><td>' . _('Academic Year') . ":</td>
<td><select name='year'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Year');
$sql="SELECT * FROM years";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result))
{
	echo '<option value='. $myrow['id'].  '>'.' '.$myrow['year'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr>';
DB_data_seek($result,0);
echo '</select></td></tr>';
echo '<tr><td>' . _('Class') . ":</td>
<td><select name='student_class'>";
echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
$sql="SELECT * FROM gradelevels";
$result=DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) 
{
	echo '<option value='. $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop
DB_data_seek($result,0);
echo '</select></td></tr></table>';
echo '<table class=enclosed>';
echo "<div class='centre'><input  type='Submit' name='submit' value='" . _('Submit') . "'></div>";
if (isset($_POST['submit'])) 
{
	$_SESSION['class'] = $_POST['student_class'];
	$_SESSION['year'] = $_POST['year'];	
	$sql2="SELECT year FROM years
	WHERE id='".$_SESSION['year'] ."'";
	$result2=DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$yearname=$myrow2['year'];
	
	$sql="SELECT grade_level FROM gradelevels
	WHERE id='".$_SESSION['class'] ."'";
	$result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
			
	?>
	
	<table class=enclosed>
	<tr><td>Class</td>
	<td><?php echo $myrow['grade_level']; ?></td>
	</tr>
	<tr> 
	<td>Academic Year</td>
	 <td><?php echo $yearname; ?></td></tr>
	 <?php
	echo "<TR><td><INPUT TYPE=SUBMIT NAME='Bill' VALUE='" . _('Bill Class') . "'></td></tr>";	
	echo "</table>";
}
include('includes/footer.inc');
?>
