package {"epel-release":
  provider => rpm,
  ensure => installed,
  source => "http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm",
}

package {"remi-release":
  provider => rpm,
  ensure => installed,
  source => "http://rpms.famillecollet.com/enterprise/remi-release-6.rpm",
}

exec { "enable-remi":
  command => "/usr/bin/yum-config-manager --enable remi",
  require => Package["remi-release"],
}

exec { "enable-remi-php55":
  command => "/usr/bin/yum-config-manager --enable remi-php55",
  require => Package["remi-release"],
}

package {"wget":
  provider => yum,
  ensure => installed
}
