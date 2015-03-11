class devbox {
  import "repositories.pp"
  import "dev-apache.pp"
  import "dev-php.pp"
}

#class{'grunt::install':}
#include nodejs

include devbox