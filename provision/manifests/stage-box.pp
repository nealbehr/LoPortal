#main bootstrap

class stagebox {
  import "repositories.pp"
  import "stage-apache.pp"
  import "dev-php.pp"
}

#include nodejs

include stagebox