class faster_apache::modspdy (
  $url     = $faster_apache::params::apache_mod_spdy_url,
  $package = $faster_apache::params::apache_mod_spdy_package,
  $phpcgi  = $faster_apache::params::apache_mod_spdy_cgi,
  $ensure  = 'present'
) {

  if $::osfamily == 'Debian' {
    if ! defined(Package['libapache2-mod-fcgid']) {
      package { 'libapache2-mod-fcgid':
        ensure => present,
        notify => Service['httpd']
      }
    }
  } elsif $::osfamily == 'Redhat' {
    if ! defined(Package['mod_fcgid']) {
      package { 'mod_fcgid':
        ensure  => present,
        notify => Service['httpd']
      }
      file { "/tmp/fcgidsock":
        ensure => directory,
        mode => 777,
        require => Package['mod_fcgid'],
      }
      file { "${faster_apache::params::confd_dir}/fcgid.conf":
        content => template("faster_apache/spdy/fcgid_conf.erb"),
        ensure  => $ensure,
        purge   => false,
        require => Package['mod_fcgid'],
      }
    }
  }

  $download_location = $::osfamily ? {
    'Debian' => '/tmp/mod-spdy.deb',
    'Redhat' => '/tmp/mod-spdy.rpm'
  }

  $provider = $::osfamily ? {
    'Debian' => 'dpkg',
    'Redhat' => 'yum'
  }

  exec { "download apache mod-spdy to ${download_location}":
    creates => $download_location,
    command => "wget ${url} -O ${download_location}",
    timeout => 30,
    path    => '/usr/bin'
  }

  yumrepo { "mod-spdy":
    baseurl  => "http://dl.google.com/linux/mod-spdy/rpm/stable/x86_64",
    descr    => "SPDY repository",
    enabled  => 1,
    gpgcheck => 0
  }

  package { $package:
#    install_options => [' --nodeps'],
    ensure          => $ensure,
    provider        => $provider,
    source          => $download_location,
    require         => Yumrepo['mod-spdy'],
    notify          => Service['httpd'],
  }

  file { [
    "${faster_apache::params::mod_dir}/spdy.load",
    "${faster_apache::params::mod_dir}/php5filter.conf"
  ] :
    purge => false,
  }

  if $faster_apache::params::mod_enable_dir != undef {
    file { [
      "${faster_apache::params::mod_enable_dir}/spdy.load",
      "${faster_apache::params::mod_enable_dir}/spdy.conf",
      "${faster_apache::params::mod_enable_dir}/php5filter.conf"
    ] :
      purge => false,
    }
  }

  file { "${faster_apache::params::confd_dir}/spdy.conf":
    content => template("faster_apache/spdy/spdy_conf.erb"),
    ensure  => $ensure,
    purge   => false,
    require => Package[$package]
  }

  file { '/usr/local/bin/php-wrapper':
    content => template("faster_apache/spdy/php-wrapper.erb"),
    ensure  => $ensure,
    mode    => 0775,
    purge   => false,
    require => Package[$package]
  }

  file {'/etc/httpd/conf.d/ssl.load':
    content => 'LoadModule ssl_module modules/mod_ssl_with_npn.so',
  }
}
