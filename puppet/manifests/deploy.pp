Package {  allow_virtual => false }

node default {

  include ::epel
  include ::configure

  include ::apache
  create_resources(::apache::vhost, hiera('apache::vhosts', {}))

  include ::percona
  create_resources(::percona::database, hiera('percona::database', {}))
  create_resources(::percona::rights  , hiera('percona::rights'  , {}))

  php::ini {'/etc/php.ini':}
  include ::php::cli
  include ::php::mod_php5
  create_resources(::php::module, hiera('php::module',{}))
  create_resources(::php::module::ini, hiera('php::module::ini',{}))

#  include ::git
  include ::imagemagick

  include ::iptables
#  include ::nodejs

#  package {"libpng":
#    provider => yum,
#    ensure => installed
#  }

#  package { 'grunt-cli':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }

#  package { 'grunt':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#
#  package { 'grunt-contrib-concat':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#  package { 'grunt-contrib-uglify':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#  package { 'grunt-processhtml':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#  package { 'grunt-string-replace':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#  package { 'grunt-contrib-imagemin':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
#  package { 'grunt-contrib-cssmin':
#    ensure   => present,
#    provider => 'npm',
#    require => Package['npm'],
#  }
}