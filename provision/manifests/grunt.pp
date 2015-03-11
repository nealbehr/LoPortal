exec { 'npm update':
  command => 'sudo npm install npm -g',
  path => '/usr/bin/',
  environment => 'clean=yes',
  require => Package['npm'],
}

package { 'grunt-cli':
  ensure   => present,
  provider => 'npm',
  require => Package['npm'],
}

package { 'bower':
  ensure   => present,
  provider => 'npm',
  require => Package['npm'],
}

package {"libpng":
  provider => yum,
  ensure => installed
}