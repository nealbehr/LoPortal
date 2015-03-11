php::ini {'/etc/php.ini':
  date_timezone   => 'America/Los_Angeles',
}

include php::cli

include php::mod_php5

php::module {
  [
    'opcache',
    'process',
    'pecl-xdebug',
    'mbstring',
    'pear',
    'pear-DB'
  ]:
}

php::module::ini { 'opcache':
  settings => {
    'opcache.enable'                  => '1',
    'opcache.memory_consumption'      => 128,
    'opcache.interned_strings_buffer' => 8,
    'opcache.max_accelerated_files'   => 4000,
    'opcache.revalidate_freq'         => 60,
    'opcache.fast_shutdown'           => 1,
    'opcache.enable_cli'              => 1
  },
  zend => '/usr/lib64/php/modules'
}

php::module::ini { 'pecl-xdebug':
  settings => {
    'xdebug.remote_autostart' => '0',
    'xdebug.remote_enable'    => '0',
    'xdebug.profiler_enable'  => '0',
  },
  zend => '/usr/lib64/php/modules'
}