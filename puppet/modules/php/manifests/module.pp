# Define: php::module
#
# Manage optional PHP modules which are separately packaged.
# See also php::module:ini for optional configuration.
#
# Sample Usage :
#  php::module { [ 'ldap', 'mcrypt', 'xml' ]: }
#  php::module { 'odbc': ensure => absent }
#  php::module { 'pecl-apc': }
#
define php::module ( $ensure = installed ) {
    package { "php-${title}":
        ensure => $ensure,
        require => [
          Yumrepo["remi-test"],
        ],
    }
}

