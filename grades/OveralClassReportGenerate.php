<?php
function mode_marks($starndard,$period_id,$exam_mode,$db) {
	$sql = "select dm.id,SUM(sm.actual_marks) as actual_marks,dm.class_id  from debtorsmaster dm
	INNER JOIN studentsmarks sm ON sm.student_id=dm.id
	INNER JOIN subjects sub ON sub.id=sm.subject_id
	INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
	WHERE  sm.period_id='$period_id'
	AND sm.exam_mode='$exam_mode'
	AND sub.display=1
	AND dm.grade_level_id='$starndard'
	GROUP BY sm.student_id
	ORDER BY actual_marks DESC";

	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$marks_array[] = array("id" => $row['id'],
				     "actual_marks" => $row['actual_marks'],
					 "stream" => $row['class_id']);
	}
	return $marks_array;
		}
function get_standards($db) {
	$sql = "select id,grade_level FROM gradelevels
	WHERE grade_level NOT LIKE 'None'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$standards_array[] = array("id" => $row['id'],
				     "grade_level" => $row['grade_level']);
	}
	return $standards_array;
}
function get_exams($db) {
	$sql = "select * FROM markingperiods";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$id_array[] = array("id" => $row['id'],
				     "title" => $row['title']);
	}
	return $id_array;
}
function tep_get_students($class,$period_id,$db) {
	$sql = "select DISTINCT(rs.student_id),dm.*,SUM(sm.marks) as totalmarks from registered_students rs
	INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
	INNER JOIN studentsmarks sm ON sm.student_id=rs.student_id
	INNER JOIN classes cl ON cl.id=rs.class_id
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
	WHERE cl.grade_level_id='$class'
	AND rs.period_id='$period_id'
	GROUP BY sm.student_id
	order by totalmarks DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students_array[] = array("id" => $row['id'],
				     "name" => $row['name']);
	}
	return $students_array;
}
function tep_set_students($debtorno,$period,$db) {
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
function students_subjects_class($student,$period,$db) {
   $sql = "select COUNT(rs.subject_id) from registered_students rs
	INNER JOIN subjects sub ON sub.id=rs.subject_id
	WHERE rs.student_id='$student'
	AND rs.period_id='$period'";

	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_subjects=$row[0];

	return $num_of_subjects;
}
function students_subjects($student,$period,$db) {
	$sql = "select COUNT(subject_id) from registered_students
	WHERE student_id='$student'
	AND period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_subjects=$row[0];

	return $num_of_subjects;
}
function tep_get_subjects($class,$period,$db) {
	$sql = "select sub.* from subjects sub
	INNER JOIN registered_students rs ON rs.subject_id=sub.id
	INNER JOIN classes cl ON cl.id=rs.class_id
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
	WHERE gl.id='$class'
	AND rs.period_id='$period'
	GROUP BY rs.subject_id
	order by sub.priority";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$subjects_array[] = array("id" => $row['id'],
				     "subject_code" => $row['subject_code'],
					 "subject_name" => $row['subject_name'],
					 "department_id" => $row['department_id']);
	}
	return $subjects_array;
}
function primary_get_subjects_marks_class($subject_id,$student_id,$class,$period,$calendar_id,$db) {
	$sql = "select COUNT(mp.exam_type_id) as no_of_cats from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE   sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_exams=$row[0];

	$sql = "select COUNT(mp.exam_type_id) as max_no_of_cats from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN classes cl ON cl.grade_level_id=rs.class_id
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
	WHERE  mp.exam_type_id=1
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'
	AND gl.id='$class'
	GROUP BY sm.student_id
	ORDER BY max_no_of_cats DESC LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$max_num_of_cats=$row[0];

	$sql = "select SUM(sm.marks) as marks from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN subjects sub ON sub.id=rs.subject_id
	WHERE sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'
	AND sub.display=1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$marks=$row[0];

	if($max_num_of_cats > 0){
	$average_marks=number_format($marks/$max_num_of_cats,0);
	}
	else{
	$average_marks='';
	}


	return $average_marks;
}
function tep_get_subjects_marks($subject_id,$student_id,$class,$period,$calendar_id,$db) {
	$sql = "select COUNT(mp.exam_type_id) as no_of_cats from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_cats=$row[0];

	$sql="SELECT COUNT(*) as counted FROM studentsmarks sm
    INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN classes cl ON cl.id=sm.class_id
	INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
    WHERE sm.subject_id='$subject_id'
	and sm.period_id='$period'
	AND gl.id='$class'
	AND mp.exam_type_id=1
    GROUP BY sm.student_id ORDER BY counted DESC LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$max_num_of_cats=$row[0];

	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$cat_marks=$row[0];

	if($max_num_of_cats > 0){
	$average_marks=number_format($cat_marks/$max_num_of_cats,0);
	}
	else{
	$average_marks='';
	}
	$sql = "select SUM(sm.marks) as exam_marks from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id !=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$exam_marks=$row[0];
	$real_marks=$exam_marks+$average_marks;

	if($real_marks > 0){
	$real_marks=number_format($real_marks,0);
	}
	else{
	$real_marks='';
	}
	return $real_marks;
}


class student {

var $debtorno;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student($debtorno,$db) {
	$sql = "select * from debtorsmaster where id = '$debtorno' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->debtorno = $debtorno;
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars($class,$student_id,$period,$calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled extends student {
	var $calendar_id;
	var $start_date;
	var $subject;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled($debtorno,$db) {
		$this->student($debtorno,$db);
	}



	function set_calendar_vars($class,$student_id,$period,$calendar_id,$db) {
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_subjects($class,$period,$db);
		foreach ($subjects_array as $r=>$s) {
			$this->subject[] = array("id" => $s['id'],
									"department_id" => $s['department_id'],
								   "tmarks" => tep_get_subjects_marks($s['id'],$student_id,$class,$period, $this->calendar_id,$db));
		}
	}

	function set_primary_vars_class($class,$student_id,$period,$calendar_id,$db) {
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_subjects($class,$period,$db);
		foreach ($subjects_array as $r=>$s) {
			$this->subject[] = array("id" => $s['id'],
									"department_id" => $s['department_id'],
								   "tmarks" => primary_get_subjects_marks_class($s['id'],$student_id,$class,$period, $this->calendar_id,$db));
		}
	}
}
class bus_report {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report($class,$period, $db) {
		$this->student = $this->get_student($db);

		$this->scheduled_students = $this->get_scheduled_students($class,$period,$db);
	}

	function get_student($db) {
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


	function get_scheduled_students($class,$period,$db) {
		$scheduled_students_array = array();
		$sql = "select rs.id,dm.name, rs.student_id from registered_students rs
		INNER JOIN studentsmarks sm ON sm.calendar_id=rs.id
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		INNER JOIN classes cls ON cls.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cls.grade_level_id
		WHERE rs.period_id='$period'
		AND gl.id='$class'
		AND sm.period_id='$period'
		GROUP BY rs.student_id";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result))
			 {
				$scheduled_students_array[] = array('id' => $row['id'],'class_id'=>$class,
				'student_id' => $row['student_id'],'totalmarks' => $row['totalmarks'],'name' => $row['name']);
			}
			return $scheduled_students_array;
		}
		else
		{

			return $scheduled_students_array;
		}
	}

function total_marks($student_id,$period_id,$db) {
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

function subject_meangrade($subject_id,$period_id,$class,$db) {
		$sql = "select SUM(sm.marks) as submarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		INNER JOIN classes cl.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE rs.subject_id='$subject_id'
		AND mp.title NOT LIKE 'Transcript'
		AND rs.period_id='$period_id'
		AND gl.id='$class'";
		//echo $query;
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['submarks'];
		}

 }

?>
