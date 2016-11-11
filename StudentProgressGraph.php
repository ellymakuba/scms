<?php

/* $Id: SalesGraph.php 4839 2012-01-25 23:03:03Z vvs2012 $*/

 include('includes/session.inc');
 include('includes/phplot/phplot.php');
 $title=_('Student Progress Graph');
 include('includes/header.inc');




 if (!isset($_POST['student_id'])){
if(isset($_GET['ID'])){
$_POST['student_id']=$_GET['ID'];
}
	echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />'; 
	echo '<table class="enclosed">
			<tr><td>' . _('Select Student:') . '</td>
			<td><select Name="student_id">';

	DB_data_seek($result, 0);
		$sql = 'SELECT id,debtorno,name FROM debtorsmaster';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['student_id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['name'];
		} //end while loop
		echo '</select></td></tr>';
		
	
	echo '<tr><td>'._('Graph Type').'</td>';
	echo '<td><select name="GraphType">';
	echo '<option value="bars">'._('Bar Graph').'</option>';
	echo '<option value="stackedbars">'._('Stacked Bar Graph').'</option>';
	echo '<option value="lines">'._('Line Graph').'</option>';
	echo '<option value="linepoints">'._('Line Point Graph').'</option>';
	echo '<option value="area">'._('Area Graph').'</option>';
	echo '<option value="points">'._('Points Graph').'</option>';
	echo '<option value="pie">'._('Pie Graph').'</option>';
	echo '<option value="thinbarline">'._('Thin Bar Line Graph').'</option>';
	echo '<option value="squared">'._('Squared Graph').'</option>';
	echo '<option value="stackedarea">'._('Stacked Area Graph').'</option>';
	echo '</select></td></tr>';

	echo '</table>';

	echo '<br /><div class="centre"><input type="submit" Name="ShowGraph" Value="' . _('Show Student Graph') .'" /></div>';
	include('includes/footer.inc');
} else {

		
		$sql="SELECT name FROM debtorsmaster 
		WHERE id='".$_POST['student_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$student_name=$myrow['name'];

	$graph = new PHPlot(950,450);
	$GraphTitle ='';
	

	$GraphTitle .= ' ' . _('Progress Graph for' . ' '.$student_name);
	

	$SQL = "SELECT mean,period_id FROM termly_class_ranks
	WHERE student_id='".$_POST['student_id']."'
	GROUP BY period_id";


	$graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile('companies/' .$_SESSION['DatabaseName'] .  '/reports/student.png');
	$graph->SetXTitle(_('Periods'));
	$graph->SetYTitle(_('Student Mean'));
	
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
	$graph->SetBackgroundColor('white');
	$graph->SetTitleColor('blue');
	$graph->SetFileFormat('png');
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline('1');
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetDataType('text-data');

	$result = DB_query($SQL, $db);
	if (DB_error_no($db) !=0) {

		prnMsg(_('The mean graph data for selected period could not be retrieved because') . ' - ' . DB_error_msg($db),'error');
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($result)==0){
		prnMsg(_('There is no mean to graph'),'info');
		include('includes/footer.inc');
		exit;
	}

	$GraphArrays = array();
	$i = 0;
	while ($myrow = DB_fetch_array($result)){
		$GraphArray[$i] = array($myrow['period_id'],$myrow['mean']);
		$i++;
	}

	$graph->SetDataValues($GraphArray);
	$graph->SetDataColors(
		array('grey','wheat'),  //Data Colors
		array('black')	//Border Colors
	);
	//$graph->SetLegend(array(_('Actual'),_('Budget')));

	//Draw it
	$graph->DrawGraph();
	echo '<table class="selection">
			<tr><td>';
	echo '<p><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/student.png" alt="Student Progress Graph"></img></p>';
	echo '</td>
		</tr>
		</table>';
	include('includes/footer.inc');
}
?>