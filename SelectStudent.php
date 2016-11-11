<?php
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Manage Students');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
echo "<br><form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';
	
echo '<tr><td>' . _('Search Student RegNo/Name') . ':<input type="Text" name="searchval" 
  size=30   maxlength=20></td>
		<td><input  type="submit" name="form1" value="submit"></td></tr>';	
    echo '<tr><th>' . _('Name') . ':</th>
		<th>' . _('AdmNo') . ':</th>
		<th>' . _('Bill Student') . ':</th>
		<th>' . _('View Statements') . ':</th>
		<th>' . _('Exam Report') . ':</th>
		<th>' . _('End Term Report') . ':</th>
		<th>' . _('Graph') . ':</th>';		
  if (isset($_GET['pageno'])) {
   $pageno = $_GET['pageno'];
} else {
   $pageno = 1;
}
$sql = "SELECT count(*) FROM debtorsmaster";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$numrows = $query_data[0];
			
$targetpage = "SelectStudent.php";
$rows_per_page = 5;
$lastpage      = ceil($numrows/$rows_per_page);
$pageno = (int)$pageno;
if ($pageno > $lastpage) {
   $pageno = $lastpage;
} // if
$limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;	
$SearchString = '%' . str_replace(' ', '%', $_POST['searchval']) . '%';
if (isset($_POST['form1'])){
$sql = "SELECT * FROM debtorsmaster WHERE debtorno LIKE  '". $SearchString."'
		OR name LIKE  '". $SearchString."'";
            $DbgMsg = _('The SQL that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);		
			
}		
else{
$sql = "SELECT * FROM debtorsmaster ORDER BY name $limit";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
}			
			while ($row = DB_fetch_array($result))
			{
		$passport=$row['studentPhoto'];
		 echo "<tr >";
		  echo "<td class=\"visible\">".$row['name']."</td>";
		  echo '<td class="visible"><a href="' . $rootpath .'/Students.php?&id=' . $row['id'] . '">' . $row['debtorno'] . '</a></td>';
		  echo '<td class="visible"><a href="' . $rootpath .'/StudentBilling.php?&student_id=' . $row['id'] . '">' . _('Bill Student') . '</a></td>';
		  echo '<td class="visible"><a href="' . $rootpath .'/StudentStatements.php?&student_id=' . $row['id'] . '">' . _('view statement') . '</a></td>';
		  echo '<td class="visible"><a href="' . $rootpath .'/ReportCardPerExam.php?&ID=' . $row['id'] . '">' . 
		  _('CAT/EXAM Report Card') . '</a></td>';
		  echo '<td class="visible"><a href="' . $rootpath .'/ReportCard.php?&ID=' . $row['id'] . '">' . _('End Term Report') . '</a></td>';
		  echo '<td class="visible"><a href="' . $rootpath .'/StudentProgressGraph.php?&ID=' . $row['id'] . '">' . _('Progress Graph') . '</a></td>';
		  echo "<td><img src=uploads/$passport </td>";
		    echo "</tr>";
		  $j++;
			}		

if ($pageno == 1) {
   echo "<tr><td>"." FIRST PREV ";
} else {
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=1'>FIRST</a> ";
   $prevpage = $pageno-1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$prevpage'>PREV</a> ";
}
echo " ( Page $pageno of $lastpage ) ";
if ($pageno == $lastpage) {
   echo " NEXT LAST "."</td></tr>";
} else {
   $nextpage = $pageno+1;
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$nextpage'>NEXT</a> ";
   echo " <a href='{$_SERVER['PHP_SELF']}?pageno=$lastpage'>LAST</a> ";
}
echo '</table>';			
include('includes/footer.inc');
?>
