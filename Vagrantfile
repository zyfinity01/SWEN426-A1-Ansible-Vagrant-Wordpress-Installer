# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # base box to use
  config.vm.box = "ubuntu/jammy64"
  config.vm.network "private_network", ip: "192.168.33.20"

  # Configure vagrant-vbguest plugin
  config.vbguest.auto_update = true
  config.vbguest.no_install = false
  config.vbguest.no_remote = false

  # VM specs
  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
  end

  # Provisoning config
  config.vm.provision "ansible" do |ansible|
    ansible.compatibility_mode = "2.0"
    ansible.playbook = "provisioning/playbook.yml"
  end

end
