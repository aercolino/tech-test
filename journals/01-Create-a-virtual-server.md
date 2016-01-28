### Create a virtual server

It should make things much easier for fellow engineers at Sky Bet to validate my work.


- Searched Google for xampp vs vagrant vs docker.
- Found [this page](http://www.sitepoint.com/quick-tip-get-homestead-vagrant-vm-running/).
- Opening VirtualBox from the command line prompted me about installing the latest version.
- Installed VirtualBox 5.0.14.
- Also installing the latest version of Vagrant.
- Installed Vagrant 1.8.1.
- Started following guide.
```
    $ git clone https://github.com/swader/homestead_improved ./server
    $ cd server
    $ mkdir Project
    $ cd Project
    $ mkdir public
    $ cd ../..
    $ bin/folderfix.sh
```
- Edited /etc/hosts: 192.168.10.10  skybet.challenge
- Edited Homestead.yaml
```
    sites:
        - map: skybet.challenge
          to: /home/vagrant/Code/Project/public

    $ vagrant up
```
- Extremely slow download... 30 minutes ETC
- Looked for support in Google
- Found http://stackoverflow.com/a/31246953/250838
- Issued
```
    $ (CTRL+C)
    $ rm ~/.vagrant.d/tmp/*
    $ vagrant up
```
- Download still slow, but ETC halved.
- First vagrant up ends with this error
```
    ...
    ==> default: Setting hostname...
    There was an error while executing `VBoxManage`, a CLI used by Vagrant
    for controlling VirtualBox. The command and stderr is shown below.

    Command: ["showvminfo", "99c6a563-deea-4673-81e3-860a818a6d72", "--machinereadable"]

    Stderr: VBoxManage: error: Could not find a registered machine with UUID {99c6a563-deea-4673-81e3-860a818a6d72}
    VBoxManage: error: Details: code VBOX_E_OBJECT_NOT_FOUND (0x80bb0001), component VirtualBoxWrap, interface IVirtualBox, callee nsISupports
    VBoxManage: error: Context: "FindMachine(Bstr(VMNameOrUuid).raw(), machine.asOutParam())" at line 2719 of file VBoxManageInfo.cpp
```
- Issued again
```
    $ vagrant up
```
- Second vagrant up ends with this error
```
    ...
    ==> default: Configuring and enabling network interfaces...
    The provider for this Vagrant-managed machine is reporting that it
    is not yet ready for SSH. Depending on your provider this can carry
    different meanings. Make sure your machine is created and running and
    try again. Additionally, check the output of `vagrant status` to verify
    that the machine is in the state that you expect. If you continue to
    get this error message, please view the documentation for the provider
    you're using.
```
- Issued
```
    $ vagrant status
    Current machine states:

    default                   not created (virtualbox)
```
- Getting tired of these errors :D
- Looked through the process with more attention.
- Found that I had stated 'skybet.dev' in the hosts file and 'skyebet.challenge' in the yaml file. Changed the latter to '.dev'.
- Issued
```
    $ vagrant up
```
- Nothing changed, I got the same result as the first vagrant up.
- Decided to go with the standard Laravel/Homestead guide at https://laravel.com/docs/4.2/homestead.
- Issued
```
    $ mv ./server ./hi-server
    $ vagrant box add laravel/homestead
    ==> box: Loading metadata for box 'laravel/homestead'
    box: URL: https://atlas.hashicorp.com/laravel/homestead
    This box can work with multiple providers! The providers that it
    can work with are listed below. Please review the list and choose
    the provider you will be working with.

    1) virtualbox
    2) vmware_desktop

    Enter your choice: 1
    ==> box: Adding box 'laravel/homestead' (v0.4.1) for provider: virtualbox
    The box you're attempting to add already exists. Remove it before
    adding it again or add it with the `--force` flag.

    Name: laravel/homestead
    Provider: virtualbox
    Version: 0.4.1
```
- Decided to keep the one I have.
- Issued
```
    $ git clone https://github.com/laravel/homestead.git h-server
    Cloning into 'h-server'...
    remote: Counting objects: 1454, done.
    remote: Total 1454 (delta 0), reused 0 (delta 0), pack-reused 1454
    Receiving objects: 100% (1454/1454), 229.82 KiB | 230.00 KiB/s, done.
    Resolving deltas: 100% (859/859), done.
    Checking connectivity... done.

    $ cd h-server
    $ bash init.sh
    Homestead initialized!
```
- Edited ~/.homestead/Homestead.yaml like this
```
    ---
    ip: "192.168.10.10"
    memory: 2048
    cpus: 1
    provider: virtualbox

    authorize: ~/.ssh/id_rsa.pub

    keys:
        - ~/.ssh/id_rsa

    folders:
        - map: ~/dev/interview/javascript/skybet/Code
          to: /home/vagrant/Code

    sites:
        - map: skybet.dev
          to: /home/vagrant/Code/Laravel/public

    databases:
        - homestead
```
- Edited /etc/hosts like this
```
    ...
    192.168.10.10 skybet.dev
```
- Issued
```
    $ cd ~/dev/interview/javascript/skybet/h-server
    $ vagrant up
```
- Yet again the same problem as the first vagrant up with the Homestead Improved setup.
- Had a hunch that it could be a timeout issue.
- Issued 
```
    $ vagrant up --no-destroy-on-error
    Bringing machine 'default' up with 'virtualbox' provider...
    ==> default: Importing base box 'laravel/homestead'...
    ==> default: Matching MAC address for NAT networking...
    ==> default: Checking if box 'laravel/homestead' is up to date...
    ==> default: Setting the name of the VM: homestead-7
    ==> default: Clearing any previously set network interfaces...
    ==> default: Preparing network interfaces based on configuration...
        default: Adapter 1: nat
        default: Adapter 2: hostonly
    ==> default: Forwarding ports...
        default: 80 (guest) => 8000 (host) (adapter 1)
        default: 443 (guest) => 44300 (host) (adapter 1)
        default: 3306 (guest) => 33060 (host) (adapter 1)
        default: 5432 (guest) => 54320 (host) (adapter 1)
        default: 22 (guest) => 2222 (host) (adapter 1)
    ==> default: Running 'pre-boot' VM customizations...
    ==> default: Booting VM...
    ==> default: Waiting for machine to boot. This may take a few minutes...
        default: SSH address: 127.0.0.1:2222
        default: SSH username: vagrant
        default: SSH auth method: private key
        default:
        default: Vagrant insecure key detected. Vagrant will automatically replace
        default: this with a newly generated keypair for better security.
        default:
        default: Inserting generated public key within guest...
        default: Removing insecure key from the guest if it's present...
        default: Key inserted! Disconnecting and reconnecting using new SSH key...
    ==> default: Machine booted and ready!
    ==> default: Checking for guest additions in VM...
    ==> default: Checking for host entries
    pid-file for killed process 80519 found (/Users/andrea/.vagrant.d/tmp/dns/daemon/vagrant-dns.pid), deleting.
    vagrant-dns: process with pid 81041 started.
    ==> default: Restarted DNS Service
    ==> default: adding to (/etc/hosts) : 192.168.10.10  homestead  # VAGRANT: 19318597e5f99db67e18b0618df7dff4 (default) / e6361a52-9df0-43be-96be-06fd9cff37ca
    Password:
    ==> default: Setting hostname...
    ==> default: Configuring and enabling network interfaces...
    ==> default: Mounting shared folders...
        default: /vagrant => /Users/andrea/dev/interview/javascript/skybet/h-server
        default: /home/vagrant/Code => /Users/andrea/dev/interview/javascript/skybet/Code
    ==> default: Running provisioner: file...
    ==> default: Running provisioner: shell...
        default: Running: inline script
    ==> default: ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAzIGduWE++dhaHSCwyqQf8oHx2b7rLTRkNeATq1PTcK57eCvT8M9+0snl9ai3BrlQ3tE/opJfjMKH9fSb9N6JB37UlwLsViwjY+69cGPZtYMSpprw+w/Ri1OhigjM8+FtwOvv3MFgGfjxUmnikrNH0Ml/VHgCZ3SSz+QpCdpMhTLFy/usR7k7h+9rcIonNnxxy+Syo+DqvsbmR2vBYsSg83XP2ScjskRs9DtqEr7gsDRhUoCygWarZWamCKslaA7ScXotbN/elTY3+zpsH2vODmIQsIojtOuFLrkz3IZsWtVyzVsiqq8ngpQNqh5EpGrhoI3DR1HnTFNhixr0LdOWqw== cappuccino.e.cornetto@gmail.com
    ==> default: Running provisioner: shell...
        default: Running: inline script
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-6p55yk.sh
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-jx1nj0.sh
    ==> default: nginx stop/waiting
    ==> default: nginx start/running, process 1983
    ==> default:  * Restarting PHP 7.0 FastCGI Process Manager php-fpm7.0
    ==> default:    ...done.
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-1e5cq02.sh
    ==> default: mysql:
    ==> default: [Warning] Using a password on the command line interface can be insecure.
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-c2z6r7.sh
    ==> default: createdb: database creation failed: ERROR:  database "homestead" already exists
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-11icaez.sh
    ==> default: Running provisioner: shell...
        default: Running: inline script
    ==> default: You are running composer with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug
    ==> default: Updating to version cd21505c8d58499a9b4d38573881cfec49c51ffd.
    ==> default:     Downloading: Connecting...
    ==> default:
    ==> default:     Downloading: 100%
    ==> default:
    ==> default:
    ==> default: Use composer self-update --rollback to return to version 837fa805ec9f8dcb1e05e0fca4099f0dab4f1e04
    ==> default: Running provisioner: shell...
        default: Running: /var/folders/98/1r_mkzbx1pl7bzk5jf6zyzrm0000gn/T/vagrant-shell20160127-80883-hpti9s.sh

    $ vagrant status
    Current machine states:

    default                   running (virtualbox)

    The VM is running. To stop this VM, you can run `vagrant halt` to
    shut it down forcefully, or you can run `vagrant suspend` to simply
    suspend the virtual machine. In either case, to restart it again,
    simply run `vagrant up`.

    $ vagrant ssh
    Welcome to Ubuntu 14.04.3 LTS (GNU/Linux 3.19.0-25-generic x86_64)

     * Documentation:  https://help.ubuntu.com/
    vagrant@homestead:~$
```
- Hurrah!!
- Created a phpinfo file at ~/dev/interview/javascript/skybet/Code/Laravel/public/index.php.
- Opened browser at http://skybet.dev and it works !!!
```
    System  Linux homestead 3.19.0-25-generic #26~14.04.1-Ubuntu SMP Fri Jul 24 21:16:20 UTC 2015 x86_64
```
- Done.
