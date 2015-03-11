
class percona::installrepos{	

  
    case $operatingsystem {
      centos, redhat: {
			    yumrepo { 'percona':
			    descr    => 'CentOS $releasever - Percona',
                baseurl	=> "http://repo.percona.com/centos/\$releasever/os/\$basearch/",
			    gpgkey   => 'http://www.percona.com/downloads/percona-release/RPM-GPG-KEY-percona',
			    enabled  => 1,
			    gpgcheck => 0,

			  } 
		}
      debian, ubuntu: {  }
      default: { fail("Unrecognized operating system for webserver") }
    }
       
 
}
  

