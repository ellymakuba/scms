<?php
function tep_get_students_stream($class,$period,$db) {
	$sql = "select DISTINCT(rs.student_id),dm.*,SUM(sm.marks) as totalmarks from registered_students rs
	INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
	INNER JOIN studentsmarks sm ON sm.student_id=rs.student_id
	WHERE rs.class_id='$class'
	AND rs.period_id='$period'
	GROUP BY sm.student_id
	order by totalmarks DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students_array[] = array("id" => $row['id'],
				     "name" => $row['name']);
	}
	return $students_array;
}
function tep_set_students_stream($debtorno,$period,$db) {

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
function students_subjects2($student,$period,$db) {
	$sql = "select gl.lower FROM debtorsmaster dm
	INNER JOIN registered_students rs ON rs.student_id=dm.id
	INNER JOIN gradelevels gl ON gl.id=rs.yos
	WHERE dm.id='$student'
	AND rs.period_id='$period'
	GROUP BY student_id";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$lower_display=$row[0];	
	if($lower_display==1){	
		$sql = "select COUNT(rs.subject_id) from registered_students rs
		INNER JOIN subjects sub ON sub.id=rs.subject_id
		WHERE rs.student_id='$student'
		AND rs.period_id='$period'
		AND sub.lower_display=1";
	}
	else{
	$sql = "select COUNT(rs.subject_id) from registered_students rs
		INNER JOIN subjects sub ON sub.id=rs.subject_id
		WHERE rs.student_id='$student'
		AND rs.period_id='$period'
		AND sub.display=1";
	}	
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_subjects=$row[0];
	
	return $num_of_subjects;
}
function tep_get_subjects_stream($class,$period,$db) {
	$sqlclass = "select gl.lower from gradelevels gl,registered_students rs ,classes cl
	WHERE rs.class_id='$class'
	AND gl.id=rs.yos
	AND cl.id=rs.class_id
	AND rs.period_id='$period'";
	$resultclass = DB_query($sqlclass,$db);
	$rowclass = DB_fetch_array($resultclass);
	$lower_display=$rowclass['lower'];
	
	if($lower_display==1){
	$sql = "select sub.* from subjects sub 
	INNER JOIN registered_students rs ON rs.subject_id=sub.id
	WHERE rs.class_id='$class'
	AND rs.period_id='$period'
	AND sub.id !=7
	GROUP BY rs.subject_id
	order by sub.priority";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) 
	{
		$subjects_array[] = array("id" => $row['id'],
						"display" => $row['display'],
				     "subject_code" => $row['subject_code'],
					 "department_id" => $row['department_id'],
					 "subject_name" => $row['subject_name']);
		}
	}
	else{
	$sql = "select sub.* from subjects sub 
	INNER JOIN registered_students rs ON rs.subject_id=sub.id
	WHERE rs.class_id='$class'
	AND rs.period_id='$period'
	GROUP BY rs.subject_id
	order by sub.priority";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) 
	{
		$subjects_array[] = array("id" => $row['id'],
						"display" => $row['display'],
				     "subject_code" => $row['subject_code'],
					 "department_id" => $row['department_id'],
					 "subject_name" => $row['subject_name']);
		}
	}
	return $subjects_array;
}
function tep_get_subjects_marks_stream($subject_id,$student_id,$class,$period,$exam_mode,$db) {
	
	$sql = "select gl.lower FROM debtorsmaster dm
	INNER JOIN registered_students rs ON rs.student_id=dm.id
	INNER JOIN gradelevels gl ON gl.id=rs.yos
	WHERE dm.id='$student_id'
	AND rs.period_id='$period'
	GROUP BY student_id";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$lower_display=$row[0];			
	if($lower_display==1){
	$sql = "select percentage_marks  from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN subjects sub ON sub.id=rs.subject_id
	WHERE sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'
	AND sm.exam_mode='$exam_mode'
	AND sub.id!=7";
	}
	else
	{
	$sql = "select actual_marks  from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'
	AND sm.exam_mode='$exam_mode'";
	}
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$actual_marks=$row[0];
	
	return $actual_marks;
	
}


class student_stream {

var $debtorno;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student_stream($debtorno,$db) {
	$sql = "select * from debtorsmaster where id = '$debtorno' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->debtorno = $debtorno;
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars_stream($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled_stream extends student_stream {
	var $calendar_id;
	var $start_date;
	var $subject;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled_stream($debtorno,$db) {
		$this->student_stream($debtorno,$db);
	}

	

	function set_calendar_vars_stream($class,$student_id,$period,$calendar_id,$exam_mode,$db) {
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_subjects_stream($class,$period,$db);
		foreach ($subjects_array as $r=>$s) {
			$this->subject[] = array("id" => $s['id'],
									"display" => $s['display'],
									"department_id" => $s['department_id'],
								   "tmarks" => tep_get_subjects_marks_stream($s['id'],$student_id,$class,$period,$exam_mode,$db));
		}
	}

}
class bus_report_stream {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report_stream($class,$period,$exam_mode,$db) {
		$this->student = $this->get_student_stream($db);

		$this->scheduled_students = $this->get_scheduled_students_stream($class,$period,$exam_mode,$db);
	}

	function get_student_stream($db) {
		$student_array = array();
		// build where clause to exclude courses by previous choices.
		
		$sql = "select debtorno from debtorsmaster ";
		//echo $query;
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
			$student_array[] = $row['debtorno'];
		}
		return $student_array;
	}


	function get_scheduled_students_stream($class,$period,$exam_mode,$db) {
		

		$scheduled_students_array = array();
		$sql = "select rs.id,dm.name, rs.student_id,er.rank,h.initial from registered_students rs
		INNER JOIN studentsmarks sm ON sm.calendar_id=rs.id
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		INNER JOIN classes cls ON cls.id=rs.class_id 
		INNER JOIN houses h ON h.id=dm.house 
		INNER JOIN exam_ranks er ON er.student_id=dm.id
		WHERE rs.period_id='$period'
		AND rs.class_id='$class'
		AND sm.class_id='$class'
		AND sm.period_id='$period'
		AND sm.exam_mode='$exam_mode'
		AND er.exam_id='$exam_mode'
		GROUP BY rs.student_id
		ORDER BY er.rank ";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_students_array[] = array('id' => $row['id'],
												'student_id' => $row['student_id'],
												'name' =>$row['name'],
												'rank' =>$row['rank'],
												'initial' =>$row['initial']);
			}
			return $scheduled_students_array;
		}
		else
		{
			
			return $scheduled_students_array;
		}
	}
	
function total_marks_stream($student_id,$period_id,$db) {
		$sql = "select SUM(sm.marks) as smarks from studentsmarks sm
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE sm.student_id='$student_id'
		AND mp.title NOT LIKE 'Transcript'
		AND sm.period_id='$period_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['smarks'];
		}
	
function subject_meangrade_stream($subject_id,$period_id,$class,$db) {
		$sql = "select SUM(sm.marks) as submarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE rs.subject_id='$subject_id'
		AND mp.title NOT LIKE 'Transcript'
		AND rs.period_id='$period_id'
		AND rs.class_id='$class'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['submarks'];
		}

 }

?>
