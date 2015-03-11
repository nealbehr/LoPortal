class deploypercona($database='dev',
$user='dev',
$password='pass',
$sourcefile='dump.sql'){

 class{'perconadb':
   database=>$database,
   user=>$user,
   password=>$password,
   sourcefile=>$sourcefile}

}