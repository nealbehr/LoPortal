# Class: configure

class configure (
  $config_path         = undef,
  $owner               = "vagrant",
  $group               = "vagrant",
  $facebook_app_id     = undef,
  $facebook_app_secret = undef,
  $percona_host        = undef,
  $percona_database    = undef,
  $percona_user        = undef,
  $percona_password    = undef,
  $stripe_api_key      = undef,
  $stripe_api_publishable = undef,
  $paypal_mode = undef,
  $paypal_client_id = undef,
  $paypal_client_secret = undef,
) {

#  file { $config_path:
#    ensure  => "present",
#    content => template('configure/services.erb')
#  }

#  file { "/etc/ssl/cert.pem":
#    ensure  => "present",
#    mode   => 644,
#    owner  => root,
#    group  => root,
#    source => "puppet:///modules/apache/broadwaytenniscenter.pem",
#    notify  => Class['Apache::Service'],
#    require => Package['httpd']
#  }
#
#  file { "/etc/ssl/bundle.crt":
#    ensure  => "present",
#    mode   => 644,
#    owner  => root,
#    group  => root,
#    source => "puppet:///modules/apache/gd_bundle-g2-g1.crt",
#    notify  => Class['Apache::Service'],
#    require => Package['httpd'],
#  }

}