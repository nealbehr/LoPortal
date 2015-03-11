$docroot = "/var/www/bjm-web/current/web"

class apache::mod::deflate {
  apache::mod { 'deflate': }
# Template uses no variables
  file { 'deflate.conf':
    ensure  => file,
    path    => "${apache::mod_dir}/deflate.conf",
    content => template('appsorama/deflate.conf.erb'),
    require => Exec["mkdir ${apache::mod_dir}"],
    before  => File[$apache::mod_dir],
    notify  => Service['httpd'],
  }
}

class { 'apache':
  default_mods => 'true',
  sendfile     => 'Off',
  mpm_module   => 'prefork',
}

file {"/var/www/":
  before => Package['httpd'],
  ensure => directory,
}

file {"/var/www/bjm-web/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/var/www/"]
}

file {"/var/www/bjm-web/current/":
  before => Package['httpd'],
  ensure => directory,
  require => File["/var/www/bjm-web/"]
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

file {'/etc/httpd/conf/cert.pem':
  source => 'puppet:///modules/appsorama/etc/httpd/conf/cert.pem',
  path => '/etc/httpd/conf/cert.pem',
  mode => 644,
  owner => root,
  group => root,
  ensure => present,
  before => Package['httpd'],
  require => File["/etc/httpd/conf/"],
}

file {'/etc/httpd/conf/bundle.crt':
  source => 'puppet:///modules/appsorama/etc/httpd/conf/bundle.crt',
  path => '/etc/httpd/conf/bundle.crt',
  mode => 644,
  owner => root,
  group => root,
  ensure => present,
  before => Package['httpd'],
  require => File["/etc/httpd/conf/"],
}

file {'/etc/httpd/conf/bj.key':
  source => 'puppet:///modules/appsorama/etc/httpd/conf/bj.key',
  path => '/etc/httpd/conf/bj.key',
  mode => 644,
  owner => root,
  group => root,
  ensure => present,
  before => Package['httpd'],
  require => File["/etc/httpd/conf/"],
}

apache::vhost { 'fb.blackjackwithcomrades.com.443':
  custom_fragment => template('appsorama/apache_files.erb'),
  servername    => 'fb.blackjackwithcomrades.com',
  ssl           => true,
  port          => 443,
  docroot       => $docroot,
  ssl_cert      => '/etc/httpd/conf/cert.pem',
  ssl_key       => '/etc/httpd/conf/bj.key',
  ssl_chain     => '/etc/httpd/conf/bundle.crt',
  docroot_owner => 'apache',
  docroot_group => 'apache',
  override      => 'All',
  access_log    => true
}

class { 'faster_apache::params': }
class { 'faster_apache::modspdy':
  require => Class['apache'],
  notify => Service['httpd'],
}
class { 'faster_apache::modpagespeed': }