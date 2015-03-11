class { 'apache':
  default_mods => 'true',
  sendfile => 'Off',
  mpm_module => 'prefork',
}

file {"/var/www/":
  before => Package['httpd'],
  ensure => directory,
}

file {"/var/www/vhosts/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/var/www/"]
}

file {"/var/www/vhosts/dup.appsorama.com/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/var/www/vhosts/"]
}

file {"/var/www/vhosts/dup-mobile.appsorama.com/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/var/www/vhosts/"]
}

file {"/etc/httpd/":
  before => Package['httpd'],
  ensure => directory,
}

file {"/etc/httpd/conf/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/etc/httpd/"]
}

file {'/etc/httpd/conf/appsorama.com.crt':
  source => 'puppet:///modules/bday/etc/httpd/conf/appsorama.com.crt',
  path => '/etc/httpd/conf/appsorama.com.crt',
  mode => 644,
  owner => root,
  group => root,
  ensure => present,
  before => Package['httpd'],
  require => File["/etc/httpd/conf/"],
}

file {'/etc/httpd/conf/appsorama.com.pem':
  source => 'puppet:///modules/bday/etc/httpd/conf/appsorama.com.pem',
  path => '/etc/httpd/conf/appsorama.com.pem',
  mode => 644,
  owner => root,
  group => root,
  ensure => present,
  before => Package['httpd'],
  require => File["/etc/httpd/conf/"],
}

$docroot = "/var/www/vhosts/dup.appsorama.com/httpdocs/"
$docrootMobile = "/var/www/vhosts/dup-mobile.appsorama.com/httpdocs/"

apache::vhost { 'dup.appsorama.com.80':
  servername => 'dup.appsorama.com',
  port => 80,
  docroot => $docroot,
  serveraliases => ['dup.appsorama.com'],
  docroot_owner => 'apache',
  docroot_group => 'apache',
  override => 'All',
  access_log => false
}

apache::vhost { 'dup-mobile.appsorama.com.80':
  servername => 'dup-mobile.appsorama.com',
  port => 80,
  docroot => $docrootMobile,
  serveraliases => ['dup-mobile.appsorama.com'],
  docroot_owner => 'apache',
  docroot_group => 'apache',
  override => 'All',
  access_log => false
}

apache::vhost { 'dup.appsorama.com.443':
  servername => 'dup.appsorama.com',
  ssl => true,
  port => 443,
  docroot => $docroot,
  serveraliases => ['dup.appsorama.com'],
  ssl_cert => '/etc/httpd/conf/appsorama.com.crt',
  ssl_key  => '/etc/httpd/conf/appsorama.com.pem',
  docroot_owner => 'apache',
  docroot_group => 'apache',
  override => 'All',
  access_log => false
}

apache::vhost { 'dup-mobile.appsorama.com.443':
  servername => 'dup-mobile.appsorama.com',
  port => 443,
  ssl => true,
  docroot => $docrootMobile,
  serveraliases => ['dup-mobile.appsorama.com'],
  ssl_cert => '/etc/httpd/conf/appsorama.com.crt',
  ssl_key  => '/etc/httpd/conf/appsorama.com.pem',
  override => 'All',
  access_log => false
}

define configure_httpdocs_directory {
  notify { "configure_httpdocs_directory: ${title}": }

  file {"/var/www/vhosts/${title}/httpdocs_rev":
    ensure => directory,
    require => Apache::Vhost["${title}.80"],
  }

  file {"/var/www/vhosts/${title}/httpdocs_rev/1":
    ensure => directory,
    require => File["/var/www/vhosts/${title}/httpdocs_rev"],
  }

  exec {"set-current-version ${title}":
    command => "/bin/echo '1' >> /var/www/vhosts/${title}/httpdocs_rev/1/ver",
    require => File["/var/www/vhosts/${title}/httpdocs_rev/1"],
  }

  exec {"remove-httpdocs ${title}":
    command => "/bin/rmdir /var/www/vhosts/${title}/httpdocs",
    require => Exec["set-current-version ${title}"],
  }

  exec {"current-soflink ${title}":
    command => "/bin/ln -sf /vagrant /var/www/vhosts/${title}/current",
    require => Exec["remove-httpdocs ${title}"],
  }

  exec {"httpdocs-softlink ${title}":
    command => "/bin/ln -sf /var/www/vhosts/${title}/current/web /var/www/vhosts/${title}/httpdocs",
    require => Exec["current-soflink ${title}"],
  }
}

$hosts = ['dup.appsorama.com', 'dup-mobile.appsorama.com']
configure_httpdocs_directory { $hosts:
  require => Apache::Vhost["dup.appsorama.com.80"]
}
