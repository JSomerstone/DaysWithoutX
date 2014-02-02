# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "precise64"
    config.vm.box_url = "http://cloud-images.ubuntu.com/vagrant/precise/current/precise-server-cloudimg-amd64-vagrant-disk1.box"
    config.vm.provision :shell, :path => "vagrant/install.dependities.sh"
    config.vm.provision :shell, :path => "vagrant/setup.environment.sh"

    config.vm.hostname = "dayswithout.dev"
    config.vm.network "forwarded_port", guest: 80, host: 8080
    config.vm.synced_folder "./", "/vagrant", owner: "vagrant", group: "www-data"

    config.vm.provider "virtualbox" do |v|
        v.name = "dayswithout-dev"
        v.memory = 2048
        v.customize ["modifyvm", :id, "--cpus", 2]
    end
end
