# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu/xenial64"

    config.vm.hostname = "dayswithout.dev"
    # config.vm.network "forwarded_port", guest: 80, host: 8080
    config.vm.network :private_network, ip: "192.168.111.222"
    config.vm.synced_folder "./", "/vagrant", owner: "vagrant", group: "www-data"

    # First, install python
    config.vm.provision "shell" do |s|
      s.inline = "apt-get install -y python"
    end

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
