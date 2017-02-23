# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "ubuntu/xenial64"
  config.vm.provider "virtualbox" do |v|
    v.memory = 2048
    v.cpus = 1
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
  end

  config.vm.network "private_network", type: "dhcp"
  config.vm.hostname = "eldrich"
  config.ssh.insert_key = true
  config.ssh.forward_agent = true


  config.vm.synced_folder "./", "/vagrant", id: "vagrant-root",
    owner: "ubuntu",
    group: "www-data",
    mount_options: ["dmode=775,fmode=775"]

  config.vm.provision "shell", path: "config/server/setup.sh"
  config.vm.provision "shell", path: "config/server/composer.sh", privileged: false
end