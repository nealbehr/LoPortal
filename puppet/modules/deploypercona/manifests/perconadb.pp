class deploypercona::perconadb
    ($database='dev',
    $user='dev',
    $password='pass',
    $sourcefile='dump.sql'){

    percona::database { $database:
      ensure => present,
      require=>Service[$::percona::service_name],
    }
    ->
    percona::rights { "$user@localhost":
      user     => $user,
      password => $password,
      database => $database,
      priv     => "all",
      host=>'%'
    }
    ->
    percona::rights { "$user":
      user     => $user,
      database => $database,
      password => $password,
      priv     => "all",
      host=>'localhost'
    }
    ->
    file{'dump':
      path=>"/tmp/$sourcefile",
     # source=>"puppet:////../db/$sourcefile",
      source=>"/vagrant/db/$sourcefile",
      require=>Service[$::percona::service_name],
    }
    ->
    exec{'installschema':
      command=>"mysql -u$user -p$password -h localhost $database</tmp/$sourcefile",
      require=>[Service[$::percona::service_name],File['dump']],
      refreshonly => true,
      subscribe=>File['dump'],
      path    => "/usr/bin:/usr/sbin:/bin",
    }

  $grant_query="GRANT SUPER ON *.* TO $user@localhost"

  exec{'grant_mysql_query':
    command=>"mysql -u root -e \'$grant_query\'",
    require=>Percona::Rights[$user],
    path    => "/usr/bin:/usr/sbin:/bin",
  }

}