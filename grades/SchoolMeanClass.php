<?php
function tep_set_students2($debtorno,$period,$db) {
	$sql = "select id,student_id from registered_students 
	WHERE student_id='$debtorno'
	AND period_id='$period'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$ids[] = array("id" => $row['id'],
				     "student_id" => $row['student_id']);
	}
	
	return $ids;
}
function get_subjects($class,$period,$db) {
	$sql = "select DISTINCT(subm.subject_id),subm.id from subject_mean subm
	INNER JOIN subjects sub ON sub.id=subm.subject_id
	INNER JOIN classes cl ON cl.id=subm.stream
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
	WHERE gl.id='$class'
	AND subm.period_id='$period'
	GROUP BY subm.subject_id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$subjects_array[] = array("id" => $row['id'],
				     "subject_id" => $row['subject_id']);
	}
	
	return $subjects_array;
}
function get_streams($gradelevel,$period,$subject,$db) {
	$sql = "SELECT cl.id,cl.class_name FROM subject_mean subm
	INNER JOIN classes cl ON subm.stream=cl.id
	WHERE grade_level_id='$gradelevel'
	AND subm.period_id='$period'
	AND subm.subject_id='$subject'
	ORDER BY subm.mean DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$streams_array[] = array("id" => $row['id'],
				     "class_name" => $row['class_name']);
	}
	
	return $streams_array;
}

function tep_get_exam_mode_marks2($marking_period_id, $calendar_id,$db) {
	$sql = "select marks from studentsmarks where 
	exam_mode='$marking_period_id' and calendar_id='$calendar_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['marks'];
}

class stream {

var $name;
var $id;

function stream($id,$db) {
	$sql = "select * from classes where id = '$id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->id = $id;
	$this->name = $row['class_name'];
}


}

 class scheduled2 extends stream {
	var $calendar_id;
	var $start_date;
	var $exam_mode;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled2($id,$db) {
		$this->stream($id,$db);
	}

	

	function set_calendar_vars2($calendar_id,$db) {
		$sql = "select subm.*,cl.class_name from subject_mean subm
		INNER JOIN classes cl ON cl.id=subm.stream
		 where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
		$this->streams[] = array("mean" => $row['mean'],
								   "roll" => $row['roll'],
								   "class_name" => $row['class_name']);
		}						   
	}

}
class bus_report2 {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report2($class,$period,$db) {
		$this->scheduled_subjects = $this->get_scheduled_subjects($class,$period,$db);
		$this->calculate_subjects_mean = $this->calculate_subjects_mean($class,$period,$db);
	}

	
	function calculate_subjects_mean($class,$period,$db) {

		$scheduled_subjects_array = array();
		$sql = "SELECT SUM(mean) AS class_mean, subm.subject_id as subid,subm.id,sub.subject_name
		FROM class_subject_mean subm
		INNER JOIN subjects sub ON sub.id=subm.subject_id
		INNER JOIN gradelevels gl ON gl.id=subm.class
		WHERE subm.period_id='$period'
		AND gl.id='$class'
		GROUP BY subm.subject_id
		ORDER BY class_mean DESC";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_subjects_array[] = array('id' => $row['id'],
												'subject_id' => $row['subid'],
												'mean' => $row['class_mean'],
					 							"subject_name" => $row['subject_name']);
			}
			return $scheduled_subjects_array;
		}
		else
		{
			
			return $scheduled_subjects_array;
		}
	}
	function get_scheduled_subjects($class,$period,$db) {

		$scheduled_subjects_array = array();
		$sql = "SELECT subject_id,mean FROM class_subject_mean
		WHERE class_id='$class'
		AND period_id='$Period'";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_subjects_array[] = array('mean' => $row['mean'],
												'subject_id' => $row['subject_id']);
			}
			return $scheduled_subjects_array;
		}
		else
		{
			
			return $scheduled_subjects_array;
		}
	}
	


 }

?>
