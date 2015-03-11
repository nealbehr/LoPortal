Puppet-faster-Apache
====================

Puppet script for Apache2 mod_pagespeed and mod_spdy

Use it like this: 

```
    class { 'faster_apache::params': }
    class { 'faster_apache::modpagespeed': }
    class { 'faster_apache::modspdy': }
```
