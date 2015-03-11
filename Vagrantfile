# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "CentOs65"
  config.vm.box_url = "http://puppet-vagrant-boxes.puppetlabs.com/centos-65-x64-virtualbox-puppet.box"
  config.vm.hostname = "lo.portal.1rex.com"

  config.vm.network :private_network, ip: '192.168.50.9'
  config.vm.network :forwarded_port, guest: 80, host: 10080
  config.vm.network :forwarded_port, guest: 443, host: 10081

  config.vm.synced_folder '.', '/vagrant', nfs: true

  config.vm.provider "virtualbox" do |v|

    cpus = `sysctl -n hw.ncpu`.to_i
    mem = `sysctl -n hw.memsize`.to_i / 1024 / 1024 / 4
    v.customize ["modifyvm", :id, "--memory", mem]
    v.customize ["modifyvm", :id, "--cpus", cpus]
  end

  config.vm.provision :shell, :path => "puppet/bootstrap.sh"
  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = "puppet/manifests"
    puppet.manifest_file  = "deploy.pp"
    puppet.module_path = "puppet/modules"
    puppet.options = '--environment vagrant'
    puppet.hiera_config_path = "hiera.yaml"
    puppet.working_directory = "/vagrant"
  end


end
