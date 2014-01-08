# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "Ubuntu Server 12.10 amd64 Minimal"
  config.vm.box_url = "http://goo.gl/wxdwM"
  config.vm.provision :shell, :path => "vagrant/setup.environment.sh"
  config.vm.provision :shell, :path => "vagrant/install.dependities.sh"

  config.vm.network "forwarded_port", guest: 80, host: 8081

#  config.vm.provider "virtualbox" do |v|
#    v.memory = 1024
#    v.customize ["modifyvm", :id, "--cpus", 2]
#  end
end
