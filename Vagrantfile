# -*- mode: ruby -*-
# vi: set ft=ruby :

# Requires `vagrant-berkshelf` plugin for Vagrant
Vagrant.configure("2") do |config|
  config.vm.box = "precise64"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"

  config.vm.provision :chef_solo do |chef|
    chef.add_recipe "apt"
    chef.add_recipe "php54"
    chef.add_recipe "php"
 
    chef.json = {}
  end
end
