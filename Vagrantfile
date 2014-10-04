# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu-trusty-64"
    config.vm.box_url = "https://cloud-images.ubuntu.com/vagrant/trusty/current/trusty-server-cloudimg-amd64-vagrant-disk1.box"

    config.vm.hostname = "dayswithout.dev"
    # config.vm.network "forwarded_port", guest: 80, host: 8080
    config.vm.network :private_network, ip: "192.168.111.222"
    config.vm.synced_folder "./", "/vagrant", owner: "vagrant", group: "www-data"

    config.vm.provision "ansible" do |ansible|
        ansible.playbook = "ansible/development.yml"
        ansible.inventory_path = "ansible/hosts-dev"
        ansible.host_key_checking = false
        ansible.sudo = true
    end

    config.vm.provider "virtualbox" do |v|
        v.name = "dayswithout-dev"
        v.memory = 2048
        v.customize ["modifyvm", :id, "--cpus", 2]
    end
end
