# Class: imagemagick
#
# This class installs imagemagick
#
# Actions:
#   - Install the imagemagick package
#
# Sample Usage:
#  class { 'imagemagick': }
#
class imagemagick {
  package { 'ImageMagick':
    ensure => installed,
  }
}
