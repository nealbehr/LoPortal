class faster_apache::params {

  #Apache stuff
  if $::osfamily == 'Debian' {
    $httpd_dir        = '/etc/apache2'
    $mod_dir          = "${httpd_dir}/mods-available"
    $mod_enable_dir   = "${httpd_dir}/mods-enabled"
    $confd_dir        = "${httpd_dir}/conf.d"
  } else {
    $httpd_dir   = '/etc/httpd'
    $mod_dir     = "${httpd_dir}/conf.d"
    $confd_dir   = "${httpd_dir}/conf.d"
  }


  $apache_mod_pagespeed_url = $::osfamily ? {
    'Debian' => $::architecture ? {
        'i386'   => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_i386.deb',
        'amd64'  => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_amd64.deb',
        'x86_64' => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_amd64.deb'
      },
    'Redhat' => $::architecture ? {
        'i386'   => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_i386.rpm',
        'amd64'  => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_x86_64.rpm',
        'x86_64' => 'https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_x86_64.rpm'
      },
  }

  $apache_mod_pagespeed_package = 'mod-pagespeed-stable'

  $apache_mod_spdy_url = $::osfamily ? {
    'Debian' => $::architecture ? {
        'i386'   => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_i386.deb',
        'amd64'  => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_amd64.deb',
        'x86_64' => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_amd64.deb'
      },
    'Redhat' => $::architecture ? {
        'i386'   => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_i386.rpm',
        'amd64'  => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_x86_64.rpm',
        'x86_64' => 'https://dl-ssl.google.com/dl/linux/direct/mod-spdy-beta_current_x86_64.rpm'
      },
  }

  $apache_mod_spdy_package = 'mod-spdy-beta'

  $apache_mod_spdy_cgi = $::osfamily ? {
    'Debian' => '/usr/bin/php-cgi',
    'Redhat' => '/usr/bin/php-cgi'
  }


}
