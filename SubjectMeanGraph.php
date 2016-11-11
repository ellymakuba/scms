<?php

/* $Id: SalesGraph.php 4839 2012-01-25 23:03:03Z vvs2012 $*/

 include('includes/session.inc');
 include('includes/phplot/phplot.php');
 $title=_('Subjects Mean Graph');
 include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . $title. '';
 $SelectADifferentPeriod ='';
 if (!isset($_POST['period_id'])){
	echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />'; 
	echo '<table class="enclosed">
			<tr><td>' . _('Select Period:') . '</td>
			<td><select Name="period_id">';

	$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>
		
		<tr><td>' . _('Select Stream:') . '</td>
		<td><select Name="class_id">';

	$sql="SELECT * FROM classes";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'].  '>'.' '.$myrow['class_name'];
	} //end while loop
	DB_data_seek($result,0);
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
	echo '<br /><div class="centre"><input type="submit" Name="ShowGraph" Value="' . _('Show mean score Graph') .'" /></div>';
	include('includes/footer.inc');
} 
else {
            $sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year 
		WHERE cp.id='".$_POST['period_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$term_name=$myrow['title'] .' '.$myrow['year'] ;
		
		$sql="SELECT class_name FROM classes
		WHERE id='".$_POST['class_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$form_name=$myrow['class_name'];

	$graph = new PHPlot(950,450);
	$GraphTitle ='';
	

	$GraphTitle .= ' ' . _(_(' ').' '.$form_name.' '. 'Subject Mean Scores for' . ' '.$term_name);
	
	$SQL = "SELECT sub.subject_name,csm.mean FROM class_subject_mean csm
	INNER JOIN subjects sub ON sub.id=csm.subject_id
	WHERE csm.period_id='".$_POST['period_id']."'
	AND csm.class='".$_POST['class_id']."'
	ORDER BY mean DESC";

	$graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile('companies/' .$_SESSION['DatabaseName'] .  '/reports/subject.png');
	$graph->SetXTitle(_('Subjects'));
	$graph->SetYTitle(_('Subject Mean'));
	
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
		$GraphArray[$i] = array($myrow['subject_name'],$myrow['mean']);
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
	echo '<p><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/subject.png" alt="Subjects mean score Graph"></img></p>';
	echo '</td>
		</tr>
		</table>';
	include('includes/footer.inc');
}
?>