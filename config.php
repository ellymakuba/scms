<?php
$DefaultLanguage ='en_GB.utf8';
$allow_demo_mode = False;
$host = 'localhost';
$dbType = 'mysqli';
$dbuser = 'root';
// assuming that the web server is also the sql server
$dbpassword = '';
// The timezone of the business - this allows the possibility of having;
putenv('TZ=Africa/Nairobi');
$AllowCompanySelectionBox = false;
$DefaultCompany = 'school manager';
$SessionLifeTime = 120;
$MaximumExecutionTime =30000;
$CryptFunction = 'sha1';
$DefaultClock = 12;
$rootpath = dirname($_SERVER['PHP_SELF']);
if (isset($DirectoryLevelsDeep)){
   for ($i=0;$i<$DirectoryLevelsDeep;$i++){
$rootpath = substr($rootpath,0, strrpos($rootpath,'/'));
} }
if ($rootpath == '/' OR $rootpath == '\\') {;
$rootpath = '';
}
error_reporting (E_ALL & ~E_NOTICE);
?>