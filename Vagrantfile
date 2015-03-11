# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

    config.vm.box = "centos6.5"
    config.vm.box_url = "https://github.com/2creatives/vagrant-centos/releases/download/v6.5.1/centos65-x86_64-20131205.box"

    config.vm.provision :shell, :path => "provision/bootstrap.sh"

    config.vm.define "dev" do |dev|
        dev.vm.network :private_network, ip: "192.168.2.11"
        dev.vm.network :forwarded_port, guest: 80, host: 5009
        dev.vm.network :forwarded_port, guest: 443, host: 5006

        dev.vm.provision :puppet do |puppet|
            puppet.manifests_path = "provision/manifests"
            puppet.manifest_file  = "dev-box.pp"
            puppet.module_path = "provision/modules"
        end
	    dev.vm.provider :virtualbox do |v|
	        v.memory = 512
	    end
    end
end