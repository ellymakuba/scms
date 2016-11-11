<?php
echo "<script language=\"javascript\">
function disciplinary(empid)
	 {
        var url = \"disciplinary.php?empid=\"+empid;

        newwin = window.open(url,'View','width=1000,height=600,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";
 echo "<script language=\"javascript\">
  function viewemp(empid)
	 {
        var url = \"viewemp.php?empid=\"+empid;

        newwin = window.open(url,'View','width=400,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";

 echo "<script language=\"javascript\">
  function uploadpic(empid)
	 {
        var url = \"uploadpic.php?empid=\"+empid;

        newwin = window.open(url,'View','width=300,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";
 
  echo "<script language=\"javascript\">
  function promotion(empid)
	 {
        var url = \"promotions.php?empid=\"+empid;

        newwin = window.open(url,'View','width=1000,height=600,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";
 echo "<script language=\"javascript\">
  function appraisal(empid)
	 {
        var url = \"appraisal.php?empid=\"+empid;

        newwin = window.open(url,'View','width=1000,height=600,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";

echo "<script language=\"javascript\">
  function training(empid)
	 {
        var url = \"soptraining.php?empid=\"+empid;

        newwin = window.open(url,'View','width=1000,height=600,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";
   function footer()
  {
    echo "<div align=\"center\"><font size=-1>
	Copyright © 2010 Native Arrays<br>
	<strong>Credits:</strong> Project Hosting and Support Kindly Provided by Native Arrays Company</div>";
   
   echo "<script language=\"javascript\">
  function ChangePass()
	 {
        var url = \"changepass.php\";
   
        newwin = window.open(url,'View','width=500,height=400,toolbar=0,location=0,directories=0,status=0,menuBar=0,scrollbars=3');
        newwin.focus();
     }
</script>";

 
  }
  function sendemail($commentinfo,$support_email,$notify_owner_email)
  {
      
        $text= $commentinfo;
		$text=stripslashes($text);
		
        $emailm=$text; 
        $headers = "From: $support_email\n"; 
        $headers .= "Return-Path: <$support_email>\n"; 
        $headers .= "X-Sender: <$support_email>\n"; 
        $headers .= "X-Mailer: PHP\n"; //mailer 
        $headers .= "X-Priority: 1\n"; //1 UrgentMessage, 3 Medium 
		
        mail("$notify_owner_email","",$emailm,$headers); 
  }
  function getretirementdate($age,$d)
  {
    $query="select retage from hrsettings order by id desc";
	$res = $d->query($query) or die(mysql_error());
	$data=$d->fetch_object($res);
	
	if (!empty($data->retage))
      $retirementage= $data->retage;//hardcode for now obtain from hrsetup table later
	else
	  $retirementage=55;
	  
	$diff=$retirementage-$age;
    $query="select date_add(curdate(),interval $diff year) as retdate";
	$res=$d->query($query) or die(mysql_error());
	$data=$d->fetch_object($res);
	
	return $data->retdate;
  }
  function fetchfullname($memberno,$d)
  {
    $query="select fullname from prmember where memberno = ".$memberno;
	$sqlresult=$d->query($query);
    $data=$d->fetch_object($sqlresult);
	
	return $data->fullname;
  }
  function getperiodfromdate($date) 
  {
    list($day, $month, $year) = split('[/.-]', $date);
	
    $period="$year"." "."$month";
	 
	if ($period=='0000 00')
	  $period=""; 
	return $period;   
  }
  
  function updatedb($d)
  {
    
	$sqlstr="select * from prmember";
	
	$result=$d->query($sqlstr);
	
	if (!$result)
	 return -1;
   else
	 return 0;
	
    
  }
  //function loads look ups - provide the key field name,description,tablename, variable with value 
  //during editing as well as db connector object
  function Loadlookup($keyfield,$descfield,$tablename,$keyfieldval,$d)
  {
  	 $sqlstr="select $keyfield as id,$descfield as name from $tablename";
	
	 $data=$d->query($sqlstr);
				
	 while ($row=mysql_fetch_object($data))
	 {
	    if (isset($keyfieldval))
	    {
	      if ($keyfieldval==$row->id)
	        print "<option value=$row->id selected>".$row->name; 
		  else  
		    print "<option value=$row->id>".$row->name; 
	    }
	    else
		   print "<option value=$row->id>".$row->name; 
	 }
  } 
  
//same as Loadlookup but has option of passing a sql statement
function Lookup($fields_id='',$fields_value='',$selected,$sql,$d){
	$results=$d->query($sql);
	while ($row = $d->fetch_object($results)){
		$SelectedField=($row->$fields_id==$selected) ? "$selected" : "";		
		echo "<option value=" . $row->$fields_id . $SelectedField . ">" . $row->$fields_value . "</option>";
	}
}
//look up dbs from set up database
function Lookupdbs($fields_id='',$fields_value='',$selected,$sql,$link,$setupdb){
    mysql_select_db($setupdb,$link);
	echo $sql;
	
	$results=mysql_query($sql,$link) or die(mysql_error());
	
	while ($row = mysql_fetch_object($results)){
		$SelectedField=($row->$fields_id==$selected) ? " selected" : "";		
		echo "<option value=" . $row->$fields_id . $SelectedField . ">" . $row->$fields_value . "</option>";
	}
}
 
 function dateconvert($date,$func) {
  if ($func == 1){ //insert conversion
    list($day, $month, $year) = split('[/.-]', $date);
    $date = "$year-$month-$day";
    return $date;
  }
  if ($func == 2){ //output conversion
    list($year, $month, $day) = split('[-.]', $date);
    $date = "$day-$month-$year";
    return $date;
  }
}

//DATE TIME CONVERT
function datetimeconvert($fdate,$func)
{
     if ($func == 1)//insert conversion
	 {
	   $pdate=explode(" ",$fdate);
	   $ptime=$pdate[1];
	   list($day, $month, $year) = split('[/.-]', $pdate[0]);
       $year=trim($year);
       $date = "$year-$month-$day $ptime";
	   return $date;
	 }
	 
	if ($func == 2) //Output conversion
	 {
	   $pdate=explode(" ",$fdate);
	   $ptime=$pdate[1];
	   list($year, $month, $day)= split('[/.-]', $pdate[0]);
       $date = "$day-$month-$year $ptime";
	   return $date;
	 } 
}

function hasaccess($d,$appid,$userid)
{
     $sqlstr="select * from intranet_perms where userid_fk=$userid and applicationid_fk=$appid";
	 $sqlresult=$d->query($sqlstr);
	 $numrows=0;
	 $numrows=$d->numrows($sqlresult);
	 
	if  ($numrows==0)
	  return false;
	else
	 return true;
}

function sopsheader(){
  echo "<fieldset>
	  <a href=\"labhome.php\">Home</a> | <a href=\"sopmanagement.php\">Management</a> 
	  | <a href=\"sopapproval.php\">Approval</a> | <a href=\"sopsview.php\">View Sops</a> 
	  | <a href=logout.php?reset=go> Log Out </a> 
	  </fieldset><br>";
};
function createmenu($linkparams,$username,$d)
{  
 //fetch userid from username
   $query="select id,isadmin from hrusers where username like '$username'";
   $result=$d->query($query);
   $row=$d->fetch_object($result);
   $userid=$row->id;
   $isadmin=$row->isadmin;
     
 
   
	echo "<div id=\"myslidemenu\" class=\"jqueryslidemenu\">
	<ul>
	<li>
	&nbsp;&nbsp;&nbsp;
	</li>
	<li>";
	
	
	  echo "<a href=\"#\">Admin</a>
	  <ul>";
	   if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"addsettings.php\">General Settings</a></li>";
	  if  (!empty($isadmin) && ($isadmin=='y'))  echo "<li><a href=\"addbank.php\">Add Banks</a></li>";
	   if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"addunits.php\">Add Units/Branches</a></li>";
	   if  (!empty($isadmin) && ($isadmin=='y'))  echo "<li><a href=\"adddepartments.php\">Add Departments</a></li>";
	   
	  if  (!empty($isadmin) && ($isadmin=='y'))  echo "<li><a href=\"addstation.php\">Add Sections/Stations</a></li>";
	 
	   if  (!empty($isadmin) && ($isadmin=='y'))  echo "<li><a href=\"adddesignations.php\">Add Designations</a></li>";
	  if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"addjobgrps.php\">Add Job Groups</a>";
	   if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"addnationalities.php\">Add Nationalities</a>";
	  //if (hasaccess($d,12,$userid)) echo "<li><a href=\"addleavetype.php\">Add Leavetypes</a>";
	   if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"addquals.php\">Add Qualifications</a>";
	  
	  
	  
	  if  (!empty($isadmin) && ($isadmin=='y'))
	  {
	    echo  "<li><a href=\"#\">Leave Management</a>
	      <ul>
          <li><a href=\"addleavetype.php\">Add Leavetypes</a></li>
          <li><a href=\"leaveapprovals.php\">Approve Leaves</a></li>
		  </ul></li>";
	  } 
	  
	 
	  if  (!empty($isadmin) && ($isadmin=='y'))
	  {
	    echo  "<li><a href=\"#\">User Management</a>
	      <ul>
          <li><a href=\"adduser.php\">Manage Users</a></li>
          <li><a href=\"permissions.php\">Manage Permissions</a></li>
		  </ul></li>";
	  } 
	echo "</ul></li>
	<li><a href=\"#\">Employee</a>
	  <ul>";
	   if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"employees.php\">Add Employee</a></li>";
	    if  (!empty($isadmin) && ($isadmin=='y')) echo "<li><a href=\"attendance.php\">Enter Staff Attendance</a></li>";
	  echo "<li><a href=\"index.php\">Employees List</a></li>";
	  
	    echo "<li><a href=\"leave.php\">Leave Application</a></li>";
	  //echo "<li><a href=\"appliedleavelist.php\">appliedleave</a></li>";
	  
	   if (!empty($linkparams)) 
	     echo"<li><a href=\"nextofkin.php?empid=$linkparams\">Emergency Contact(s)</a></li>";
	   else
	     echo"<li><a href=\"nextofkin.php\">Emergency Contact(s)</a></li>";
	   
	  if (!empty($linkparams))
	   echo "<li><a href=\"dependants.php?empid=$linkparams\">Dependants</a></li>";
	  else
	   echo"<li><a href=\"dependants.php\">Dependants</a></li>";
		  
	 echo "</ul>
	</li>
	";

	  echo " 
	
	
	<li><a href=\"#\">Reports</a>
	  <ul>
	  <li><a href=\"#\">Employee</a>
	    <ul>";
         if (hasaccess($d,29,$userid)) echo "<li><a href=\"totalsrepgen.php\">Totals by Gender</a></li>";
		 if (hasaccess($d,16,$userid)) echo "<li><a href=\"totalstatus.php\">Totals by Status</a></li>";
		 if (hasaccess($d,19,$userid)) echo "<li><a href=\"totalsdesigrep.php\">Totals by Designation</a></li>";
		 if (hasaccess($d,21,$userid)) echo "<li><a href=\"totalsjobgrprep.php\">Totals by Job Groups</a></li>";
		 if (hasaccess($d,14,$userid)) echo "<li><a href=\"totalsrep.php\">Totals by Department</a></li>";
		 if (hasaccess($d,32,$userid)) echo "<li><a href=\"totalsrepstn.php\">Totals by Station</a></li>";
		 if (hasaccess($d,14,$userid)) echo "<li><a href=\"totalsrepbra.php\">Totals by Branches</a></li>";
		 if (hasaccess($d,17,$userid)) echo "<li><a href=\"totalservice.php\">Totals by Service Type</a></li>";
         if (hasaccess($d,27,$userid)) echo "<li><a href=\"retirementrpt.php\">Staff Retirement</a></li>";
		 if (hasaccess($d,33,$userid)) echo "<li><a href=\"newemployeesrpt.php\">New Employees</a></li>";
		 if (hasaccess($d,28,$userid)) echo "<li><a href=\"staffturnoverrpt.php\">Staff TurnOver</a></li>";
		 if (hasaccess($d,23,$userid)) echo "<li><a href=\"dependantsrpt.php\">Dependants</a></li>";
		 if (hasaccess($d,24,$userid)) echo "<li><a href=\"nokrpt.php\">Emergency Contacts</a></li>";
		echo"</ul>
	  </li>
	  <li><a href=\"#\">Training</a>
	     <ul>";
         if (hasaccess($d,15,$userid)) echo "<li><a href=\"qlevelsrep.php\">Totals by Qualifications</a></li>";
         if (hasaccess($d,25,$userid)) echo "<li><a href=\"trainingrpt.php\">Staff Training </a></li>";
		 if (hasaccess($d,26,$userid)) echo "<li><a href=\"#\">Staff Appraisal</a></li>";
		echo "</ul>
	  </li>
	  <li><a href=\"#\">Conduct</a>
	   <ul>";
         if (hasaccess($d,34,$userid)) echo "<li><a href=\"suspendedemprpt.php\">Suspended Employees</a></li>";
         //if (hasaccess($d,18,$userid)) echo "<li><a href=\"#\">Staff Vacations</a></li>";
		 if (hasaccess($d,20,$userid)) echo "<li><a href=\"disciplinaryrepdet.php\">Staff Disciplinary</a></li>";
		echo "</ul>
	  </li>
	  
	 
	  
	  </ul>
	   <li><a href=\"javascript:ChangePass()\">Change Password</a></li>
	   <li><a href=\"logout.php?reset=1\">Logout</a></li>
	   <li><font color=\"#FFFFFF\">Logged in as : $username</font></li>
	</li></ul>
	

	<br style=\"clear: left\" />
	</div>";
}
?>
