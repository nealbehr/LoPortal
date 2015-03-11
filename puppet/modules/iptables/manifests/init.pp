class iptables {

  package { "iptables":
    ensure => present
  }

  service { "iptables":
    require => Package["iptables"],
    hasstatus => true,
    hasrestart=>true,
  }


file { "/etc/sysconfig/iptables":
    owner   => "root",
    group   => "root",
    mode    => 600,
    replace => true,
    ensure  => present,
    source  => "puppet:///modules/iptables/iptables.rule",
    require => Package["iptables"],
    notify  => Service["iptables"],
  }
}