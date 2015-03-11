
class percona::postinstall {


if $::percona::server {
  php::module{['mysqlnd']:}
}

}