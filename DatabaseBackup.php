<?php
/* $Id: PcTypeTabs.php 3924 2010-09-30 15:10:30Z tim_schofield $ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Insert Marks');
include('includes/header.inc');

backup_tables('localhost','elly','masinde','*',$db);


/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$tables = '*',$db)
{
  
 
  
  //get all of the tables
  if($tables == '*')
  {
    $tables = array();
    $result = DB_query('SHOW TABLES',$db);
    while($row = DB_fetch_row($result))
    {
      $tables[] = $row[0];
    }
  }
  else
  {
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }
  
  //cycle through
  foreach($tables as $table)
  {
    $result = DB_query('SELECT * FROM '.$table,$db);
    $num_fields = DB_num_fields($result);
	$num_rows = DB_num_rows($result);
    
    $return.= 'DROP TABLE IF EXISTS '.$table.';';
    $row2 = DB_fetch_row(DB_query('SHOW CREATE TABLE '.$table,$db));
    $return.= "\n\n".$row2[1].";\n\n";
    
	
    $return.= 'INSERT INTO '.$table.' VALUES';
		
    for ($i = 0; $i < $num_fields; $i++) 
    {
	$last=0;
      while($row = DB_fetch_row($result))
      {
	  
	$last=$last+1;
	
	  
		$return.= '(';
		
        for($j=0; $j<$num_fields; $j++) 
        {
          $row[$j] = addslashes($row[$j]);
          $row[$j] = ereg_replace("\n","\\n",$row[$j]);
           if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; } 
          if ($j<($num_fields-1) and isset($row[$j])) { $return.= ','; }
        }
		if ($last==($num_rows)){
        $return.= ");\n";

		}
		else{
		$return.= "),";

		}
      }
    }
    $return.="\n\n\n";
  }
  
  //save file
  $handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
  fwrite($handle,$return);
  fclose($handle);
  prnMsg(_(' back up successful'),'success');
}	
include('includes/footer.inc');
?>


