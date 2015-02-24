# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.define "redis1" do |redis1|
     redis1.vm.box = "ubuntu/trusty64"
     redis1.vm.box_check_update = false
     redis1.vm.network "private_network", ip: "192.168.50.40"
     redis1.vm.provision "ansible" do |ansible|
       ansible.playbook = "vagrant/provisioning/master-playbook.yml"
     end
  end

  config.vm.define "redis2" do |redis2|
     redis2.vm.box = "ubuntu/trusty64"
     redis2.vm.box_check_update = false
     redis2.vm.network "private_network", ip: "192.168.50.41"
     redis2.vm.provision "ansible" do |ansible|
       ansible.playbook = "vagrant/provisioning/slave-playbook.yml"
     end
  end

  config.vm.define "sentinel1" do |sentinel1|
     sentinel1.vm.box = "ubuntu/trusty64"
     sentinel1.vm.box_check_update = false
     sentinel1.vm.network "private_network", ip: "192.168.50.30"
     sentinel1.vm.provision "ansible" do |ansible|
       ansible.playbook = "vagrant/provisioning/sentinel-playbook.yml"
     end
  end

end
