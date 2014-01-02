# -*- mode: ruby -*-
# vi: set ft=ruby :

# Requires `vagrant-berkshelf` plugin for Vagrant
unless Vagrant.has_plugin?('vagrant-berkshelf')
  msg = <<MSG
=====

 \033[31mABORT:\033[0m this project requires vagrant-berkshelf. You can install it via:

    $ vagrant plugin install vagrant-berkshelf

=====
MSG
  abort(msg) 
end

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
