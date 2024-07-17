# SWEN 426 Assignment 1 &mdash; Instructions

This document provides corrected instructions to the partially-obsolete guidance from _Ansible: From Beginner to Pro_ by Michael Heap, as well as some additional instructions on how to complete the WordPress exercise. For convenience, the corrected instructions have been unified with the text of the book. Quoted text is verbatim from the text and are the words of the original authors.

The following instructions assume that you have created a new `Assignment_1` GitLab project in your Group using the template, cloned it to a convenient place and the working directory is at the top level of the Git repository.

## Chapter 2 _The Inventory File_

This chapter introduces the Ansible `inventory.yml` file which needs to be created for this exercise: one of the **Core** requirements is that `ansible-playbook ...`, which is the typical way of running Ansible, runs without error.

### Notes for Chapter 2

The contemporary, and preferred, formatting for inventory files is YAML format, rather than the harder-to-read `k=v` (`keyword=value`) form presented in the book. It's also preferred, for improved comprehension, to use the long form of options while working through the exercise. Thus, instead of

> ```text
> ansible all -i /path/to/inventory –m ping
> ```

the preferred equivalent command is

```text
ansible all --inventory-file /path/to/inventory.yml --module-name ping
```

Without an inventory file, Ansible will fail to execute:

```text
$ ansible-playbook provisioning/playbook.yml
[WARNING]: No inventory was parsed, only implicit localhost is available
[WARNING]: provided hosts list is empty, only localhost is available. Note that
the implicit localhost does not match 'all'

PLAY [all] *********************************************************************
skipping: no hosts matched

PLAY RECAP *********************************************************************
```

In the _Running Without Vagrant_ section, the sample inventory file must be re-written in YAML form. You should check that the IP address in the `inventory.yml` file matches the IP address specified in the `Vagrantfile`, otherwise you won't be able to connect. For more information on the use of YAML format for inventories see <https://docs.ansible.com/ansible/latest/user_guide/intro_inventory.html> and the _Inventory aliases_ section of that page in particular.

You won't be able to test the inventory file with the command `ansible-playbook provisioning/playbook.yml` until after creating the Playbook at the beginning of the next chapter.

## Chapter 3 _Installing WordPress_

To follow good practice (Marking Guide) you should commit to your repository fairly frequently, and put an annotated tag on the squash commit on `main` which completes logical sections. See **Completion** requirement 4 and _Appendix: Tags_ for more information.

The original instructions have been _mostly_ updated to conform with the latest Ansible conventions and avoid deprecated options and values. `ansible-lint` <https://ansible-lint.readthedocs.io/> will generate some warnings which will be addressed in Chapter 4, e.g. `no-handler` violations. You may wish to defer addressing `ansible-lint` warnings until after you have a working WordPress installation, to simplify troubleshooting and debugging of the install.

The [`template`](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/template_module.html), [`file`](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/file_module.html), [`copy`](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/copy_module.html) and [`unarchive`](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/unarchive_module.html) modules are used in this chapter without an explicitly specified `mode` parameter. These modules will function correctly using their defaults _but_ `ansible-lint` will complain:

```shell
risky-file-permissions: File permissions unset or incorrect
```

To resolve this in order to meet **Completion**, explicitly specify `mode: 644` for files and `mode: 755` for directories. Note that the [`copy`](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/copy_module.html) module has a special string `preserve` which you can use if you are confident that the source file the correct permissions. Ansible and `ansible-lint` will generally tell you if you specify the wrong permissions.

### Chapter 3 Begins

> Before we get started, we need an environment in which to build this playbook. As we did previously, we'll be using Vagrant for this job. To do this, we'll create a new Vagrant machine so that we're starting with a clean slate.

Assuming the working directory is the top level of your cloned repository, run the following command to create the default `Vagrantfile` and `.vagrant` directory:

```text
vagrant init ubuntu/jammy64
```

> We'll need to enable networking as we did last time, except that we'll be using a different IP address, just in case you want to run the environment that we set up last time and this new environment simultaneously. In addition to setting up networking, we'll also need to allocate slightly more memory to the virtual machine, as MySQL Server 5.6 won't start up with the 480 MB that Vagrant allocated by default.

The  `Vagrantfile` should specify:

```ruby
Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/jammy64"
  config.vm.network "private_network", ip: "192.168.33.20"
  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
  end
  config.vm.provision "ansible" do |ansible|
    ansible.compatibility_mode = "2.0"
    ansible.playbook = "provisioning/playbook.yml"
  end
end
```

> This will create an empty machine with an IP address of `192.168.33.20` and 1 GB of memory allocated when you run `vagrant up`, which is more than enough to get WordPress installed and configured. Run `vagrant up` now to create your machine.
>
> ### Installing Dependencies
>
> To run WordPress, you will need three pieces of software: PHP, nginx, and MySQL. ... start by creating a simple playbook that shows that Ansible can run against your Vagrant machine.

An empty Playbook `provisioning/playbook.yml` has been provided for you.

> In `provisioning/playbook.yml`, we specify on which hosts the playbook should run as well as a set of tasks to run. You start with your standard playbook, which proves that you can talk to the environment on which you are testing. Once you've created it, run `vagrant provision` to make sure that everything will run as intended:
>
> ```yaml
> ---
> - hosts: all
>   become: true
>   tasks:
>     - name: Ping the VM to confirm connectivity
>       ansible.builtin.ping:
> ```
>
> At this point, you should run vagrant up to create your virtual machine and prove that you can connect to it.
>
> ### Installing PHP
>
> Now that you know Ansible will run, let's install PHP. WordPress will run on any version of PHP from 5.2 onward, but you should use the latest version available whenever possible.

**Note:** We won't need to use the PPA mentioned in the text because the latest major version of PHP is available via `apt` on Jammy Jellyfish.

> ... you'll want to update the apt package cache before you try to install anything. You can do this in the same task as the install, but I prefer to do it on its own so that it's clear that it's a deliberate decision to update the cache rather than a side effect of installing a package.

Add the following tasks to the Playbook:

```yaml
    - name: Update the apt cache
      ansible.builtin.apt:
        update_cache: true
        cache_valid_time: 3600
    # PHP
    - name: Install PHP
      ansible.builtin.apt:
        name: php
        state: present
```

> If you run vagrant provision again after adding these tasks, it should complete successfully. To make sure that things are working as expected, you can run `vagrant ssh` and log in to the machine. Once you're logged in, run `php --version` and make sure that it yields something similar to the following:

```text
vagrant@ubuntu-jammy:~$ php --version
PHP 8.1.2-1ubuntu2.18 (cli) (built: Jun 14 2024 15:52:55) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.1.2, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.2-1ubuntu2.18, Copyright (c), by Zend Technologies
```

> That looks good to me, so let's continue and install all of the other PHP packages that you'll need.

```yaml
    - name: Install PHP
      ansible.builtin.apt:
        name: "{{ item }}"
        state: present
      with_items:
        - php
        - php-fpm
        - php-mysql
        - php-xml
```

> Unfortunately, installing PHP will also install Apache2, a web server that you don't want to use. There's no way around this, but you can remove it as soon as it's installed by adding the following task to your playbook:

```yaml
    - name: Remove Apache2
      ansible.builtin.apt:
        name: apache2
        state: absent
```

> ### Installing MySQL
>
> Once you have PHP installed (and Apache removed), you can move on to the next dependency, MySQL. Add the following to your playbook:

```yaml
    # MySQL
    - name: Install MySQL
      ansible.builtin.apt:
        name: "{{ item }}"
        state: present
      with_items:
        - mysql-server
        - python3-mysqldb
```

> It's good to run Ansible regularly when developing a playbook, so you should run `vagrant provision` now to install all of the PHP and MySQL packages. It may take a few minutes, but it should complete successfully.
>
> If all you want to do is to install MySQL, this is all that you need to do. However, Ansible installs MySQL with an empty root password and leaves some of the test databases accessible to anonymous users. Usually, you would run the mysql_secure_installation script to tidy up all of these files, but as you're running in an automated environment, you'll have to do the housekeeping yourself.
>
> Here are the tasks that you're going to complete with Ansible:
>
> 1. Change the default root password.
> 1. Remove anonymous users.
> 1. Remove test database and access to it.
>
> To change the default password, you need to generate a password to use. To do this, you can use the `openssl` utility to generate a 15-character password. Add the following to your playbook:

```yaml
- name: Generate a new root password
  ansible.builtin.command: openssl rand -hex 7
  register: mysql_new_root_pass
```

> Here, we use a feature of Ansible that you haven't seen before called _register_. The `register` keyword lets you save the return value of commands as a variable for use later in a playbook.
>
> The next thing to do is to remove the anonymous users and test databases. This is very straightforward, thanks to the `mysql_db` and `mysql_user` modules. You need to do this _before_ you change the root password so that Ansible can make the changes. Again, you need to add some tasks to your playbook :

```yaml
    - name: Remove anonymous users
      community.mysql.mysql_user:
        name: ""
        state: absent
    - name: Remove test database
      community.mysql.mysql_db:
        name: test
        state: absent
```

> The final thing to do is to change the root password and output it to the screen. In this situation, you'll take the value previously returned by `openssl` and pass it to a MySQL module to set the password. You need to set the password for every host that can access the database as root. Use the special `ansible_hostname` variable that evaluates to the current machine's hostname and then set the password for the three different formats used to denote `localhost`:

```yaml
    - name: Update root password
      community.mysql.mysql_user:
        name: root
        host: "{{ item }}"
        password: "{{ mysql_new_root_pass.stdout }}"
      with_items:
        - "{{ ansible_hostname }}"
        - 127.0.0.1
        - ::1
        - localhost

    - name: Output new root password
      ansible.builtin.debug:
        msg: "New root password is {{ mysql_new_root_pass.stdout }}"
```

> In MySQL, you can have a different password for a single user for each place they're connecting from. You will use `with_items` to set the password for every host that you know about, including `ansible_hostname`, a variable that is automatically populated with the current machine's hostname. To change the password, you use the `mysql_user` module and pass in a username, host, and password. In this instance, you will be passing in the `STDOUT` (the text that was returned to the terminal) from your `openssl` call as the password for the root user.
>
> You are halfway through the work required to configure MySQL securely! Let's quickly recap what you've done so far:
>
> 1. Installed MySQL server
> 1. Removed anonymous users
> 1. Removed the test database
> 1. Generated a new root password
> 1. Output the new root password to the screen
>
> This is actually quite a lot of work! At this point, your installation is secure, but you're not quite done. Ansible expects to be able to run database commands without a password, which was fine when you didn't have a root password, but will fail now that you do. You need to write out a new config file (located at `/root/.my.cnf`) containing the new root password so that the root user can run MySQL commands automatically.
>
> There are a few different options for writing files using Ansible (such as the `copy` and `template` modules). As this is a multi-line file that contains variables, you'll need to use Ansible's `template` module to populate its content. First, you need to create a folder to hold your template and create the file that you are going to copy over.

An empty configuration file `provisioning/templates/mysql/my.cnf` has been provided for you.

> Edit it and make sure that it has the following contents:
>
> ```ini
> [client]
> user=root
> password={{ mysql_new_root_pass.stdout }}
> ```
>
> You also need to tell Ansible to copy this template into your environment; this is done using the `template` module. Add the following task to your Playbook:

```yaml
    - name: Create my.cnf
      ansible.builtin.template:
        src: templates/mysql/my.cnf
        dest: /root/.my.cnf
```

> This file will contain the username and password for the root MySQL user. This is required so as to allow Ansible to make changes without user intervention.
>
> It's important to note that each time the playbook runs, a new root password will be generated for the server. While it's not a bad thing to rotate root passwords frequently, this may not be the behavior that you are seeking. To disable this behavior, you can tell Ansible not to run certain commands if a specific file exists. Ansible has a special `creates` option that determines if a file exists before executing a module.

Modify the `command:` entry of the "Generate new root password" task:

```yaml
    - name: Generate new root password
      ansible.builtin.command: openssl rand -hex 7 creates=/root/.my.cnf
      register: mysql_new_root_pass
```

> If the file `/root/.my.cnf` does not exist, `mysql_new_root_pass.changed` will be `true`. If it does exist, it will be set to `false`. You can use that in the rest of your Playbook to skip any steps that need not be run.

Here are two debug tasks which show the new root password if `.my.cnf` does not exist and show a message if it already exists:

```yaml
    # If /root/.my.cnf doesn't exist and the command is run
    - ansible.builtin.debug: msg="New root password is {{ mysql_new_root_pass.stdout }}"
      when: mysql_new_root_pass.changed
    # If /root/.my.cnf exists and the command is not run
    - ansible.builtin.debug: msg="No change to root password"
      when: not mysql_new_root_pass.changed
```

These debug tasks should not be permanently added to the Playbook.

> Once you make the change to add `creates=/root/.my.cnf`, you should add a `when: mysql_new_root_pass.changed` argument to each of the relevant tasks. After making these changes, the MySQL section of your playbook should look like this:
>
> [Please refer to the text, as needs be]
>
> Run `vagrant provision` now to generate a new root password and clean up your MySQL installation. If you run vagrant provision again, you should see that all of these steps are skipped:
>
> ```text
> TASK [Remove anonymous users] **************************************************
> skipping: [default]
> ```
>
> That's the end of your MySQL setup. You've downloaded and installed all of the packages required and secured it by disabling anonymous users and adding a root password. That's PHP and MySQL complete, but next you need to install a web server to handle the incoming requests.
>
> ### Installing nginx
>
> You need to install and configure nginx before you can start to install WordPress. nginx (which is an alternative to the well-known Apache web server) is the software that will receive HTTP requests from your users and forward them to PHP, where WordPress will handle the request and respond. There's quite a lot of configuration to be done for nginx. We will walk through this once we have nginx installed. Now, let's install nginx by adding the following to the end of `playbook.yml`:

```yaml
    # nginx
    - name: Install nginx
      ansible.builtin.apt:
        name: nginx
        state: present
    - name: Start nginx
      ansible.builtin.service:
        name: nginx
        state: started
```

> Run `vagrant provision` again to install nginx and start it running. If you visit `http://192.168.33.20` in your web browser [note that it is `http`_not_ `https`], you will see the "Welcome to nginx" page.

Actually, you will see the "Apache2 Default Page" _served_ by nginx. The situation is described by the answer to the question [Why do I still see an Apache site on Nginx?](https://askubuntu.com/questions/642238/why-do-i-still-see-an-apache-site-on-nginx/642288#642288) on Ask Ubuntu. This could be corrected by adding to the Playbook a task to remove the Apache2 file using the [ansible.builtin.file module](https://askubuntu.com/questions/642238/why-do-i-still-see-an-apache-site-on-nginx/642288#642288).

> This [a web server welcome page] isn't what you want your users to see. You want them to see WordPress! Thus, you need to change the default nginx virtual host to receive requests and forward them.

A functional nginx configuration file is provided for you in the Template project, located at `provisioning/templates/nginx/default`. This file was created based on the nginx configuration for WordPress guidance found at <https://developer.wordpress.org/advanced-administration/server/web-server/nginx>. Add the following task to the Playbook to copy the configuration file from the local machine to the virtual host on provisioning:

```yaml
    - name: Create nginx config
      ansible.builtin.template:
        src: templates/nginx/default
        dest: /etc/nginx/sites-available/default
```

> ### Tasks and Handlers
>
> Once you run `vagrant provision`, your config file will be up to date. However, nginx needs to be restarted in order to pick up the changes that you made to the configuration file. You could add a task to restart nginx by adding the following to the end of your playbook:

```yaml
    - name: restart nginx
      ansible.builtin.service:
        name: nginx
        state: restarted
```

> However, this would restart nginx every time the playbook is run. The better way to deal with things that need to be restarted when other things change is to use a handler. Handlers are just like tasks, but they can be triggered from anywhere. Delete the `Restart nginx` task if you added it and add the following to the bottom of your playbook. `handlers:` should be at the same level and indentation as `tasks:`

```yaml
  handlers:
    - name: restart nginx
      ansible.builtin.service:
        name: nginx
        state: restarted
```

> This code will use the `ansible.builtin.service` module to restart nginx any time the handler is triggered.

The `Restart nginx` handler can be triggered by adding `notify: restart nginx` to the `Create nginx config` task thus:

```yaml
    - name: Create nginx config
      ansible.builtin.template:
        src: templates/nginx/default
        dest: /etc/nginx/sites-available/default
      notify: restart nginx
```

> If you run `vagrant provision` now, the handler will not be run. This is because you just ran `vagrant provision` and deployed the nginx configuration, and Ansible has detected that there are no changes required.
>
> You've made quite a lot of changes, running `vagrant provision` after each change. This feels like a good opportunity to run `vagrant destroy` followed by `vagrant up` to confirm that everything is installed and configured correctly.
>
> After running vagrant up, your new config should roll out and nginx should be restarted.

**Note:** at this point the book recommends editing the `/etc/hosts` file on your local (host) machine to associate the domain name `book.example.com` with the IP address of the private network entry in the `Vagrantfile`. This is not necessary to complete the exercise _and_ will not be possible on the ECS workstations. The only difference this will make to the exercise is that you must visit <http://192.168.33.20> (which should be the IP address specified in the `Vagrantfile`) in your browser to access the WordPress site.

> ### Downloading WordPress
>
> Now that your environment has been created, you can finally download WordPress. You have two options available to do this: you can either download WordPress yourself and use Ansible to copy it into your environment, or you can have Ansible download WordPress for you.
>
> Each of these approaches has its pros and cons. If you download yourself, you'll know exactly what you're getting, but then you'll have to take the time to upgrade WordPress yourself. If you download it automatically, you will always have the latest version, but you'll have no guarantee that things will work in the same way that they did the last time you ran the playbook.

Manual download of the WordPress install will complete the **core** requirements, automating the download is one of the **challenges**. Manual download is presented here.

> #### Downloading it Yourself
>
> If you want to download WordPress yourself, you can go to <https://wordpress.org> and download the latest release. Create a folder within your provisioning folder called files and place it in there, naming it wordpress.zip . Alternatively, you can download the latest release with a command-line HTTP client named curl:

```text
curl --create-dirs --output provisioning/files/wordpress.zip https://wordpress.org/latest.zip
```

> The next step is to copy this into your environment. You only need it temporarily, so you'll copy it into the /tmp directory by adding the following to your playbook under the tasks section:

```yaml
    # WordPress
    - name: Copy wordpress zip file into /tmp
      ansible.builtin.copy:
        src: files/wordpress.zip
        dest: /tmp/wordpress.zip
```

> That's all there is to it. Each time you run Ansible, it will copy WordPress into your environment, ready to use. You'll get the same version each time, and once you're ready to upgrade, all you need to do is download a new file and overwrite `files/wordpress.zip`. Any time that you run Ansible after that, it will use the new version.
>
> ### Configuring a WordPress Install
>
> You're almost there! You have all of your dependencies installed, and you have WordPress downloaded. It's time to unzip your release and get your blog up and running.
>
> The first thing that you'll need to do is to extract `wordpress.zip`. Ansible ships with a module named `unarchive` that knows how to extract several different archive formats:

```yaml
    - name: Unzip WordPress
      ansible.builtin.unarchive:
        src: /tmp/wordpress.zip
        dest: /tmp
        remote_src: true
        creates: /tmp/wordpress/wp-settings.php
```

> You should be getting more familiar with the arguments to most modules by now. Both `src` and `dest` are showing up time and time again, for example.

The `Unzip WordPress` task introduces a new argument `remote_src: true` which "indicates the archived file is already on the remote system and not local to the Ansible controller," as described in the [ansible.builtin.unarchive](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/unarchive_module.html#ansible-collections-ansible-builtin-unarchive-module-parameter-remote-src) documentation. The `copy` option described in the textbook is deprecated in favour of `remote_src`.

> If you run your playbook, Ansible will encounter an error when it tries to extract WordPress:

```text
TASK [Unzip WordPress] *********************************************************
fatal: [default]: FAILED! => {"changed": false, "msg": "Failed to find handler
for \"/tmp/wordpress.zip\". Make sure the required command to extract the file
is installed. ... "}
```

> This error is displayed because, by default, `unzip` is not installed. I like to have a task install all of the common tools that I'll need right at the top of my tasks list. Add this to your playbook (before you install PHP):

```yaml
    - name: Install required tools
      ansible.builtin.apt:
        name: "{{ item }}"
        state: present
      with_items:
        - unzip
```

> If you run Ansible again after adding the task to install unzip, your playbook will complete successfully. The zip file contained a folder named wordpress, which means that all of the files that you need are located at /tmp/wordpress. However, this isn't where you told nginx that your application lives, so let's copy all of the files that you'll need into the correct location.

The [ansible.builtin.copy](https://docs.ansible.com/ansible/latest/collections/ansible/builtin/copy_module.html) module and `remote_src` option support recursive copying of directories and there is no need for `creates: /var/.../wp-settings.php` because the default behaviour is to replace remote files when their contents differ from the source.

```yaml
    - name: Create project folder
      ansible.builtin.file:
        dest: /var/www/book.example.com
        state: directory
    - name: Copy WordPress files
      ansible.builtin.copy:
        mode: preserve
        remote_src: true
        src: /tmp/wordpress/
        dest: /var/www/book.example.com
```

> Once this has run, visit <http://192.168.33.20> in your web browser; you should see a WordPress installation screen. It tells you that you'll need to know all of your database credentials to start the installation process. You will not want to give WordPress root access to your database, so let's create a dedicated MySQL user to use by adding the following tasks to your playbook:

```yaml
    - name: Create WordPress MySQL database
      community.mysql.mysql_db:
        name: wordpress
        state: present
    - name: Create WordPress MySQL user
      community.mysql.mysql_user:
        name: wordpress
        host: localhost
        password: bananas
        priv: wordpress.*:ALL
```

> This will create a database called `wordpress` and a user called `wordpress` with the password `bananas`. The new user will have all of the privileges on the `wordpress` database, but nothing else. After running Ansible to create the database and user (i.e. `vagrant provision`), go back to your web browser and continue the installation process.

Visiting <http://192.168.33.20> should display a page titled "WordPress > Setup Configuration File" with text "Welcome to WordPress. Before getting started, ..." and a button `Let's go!`. Clicking through that page leads to a page with text "Below you should enter you database connection details." The **Database Name**, **Username** and **Password** fields must be populated with the values set by the two tasks above. The **Database Host** and **Table Prefix** fields default to the correct values.

**Note:** the **Database Name** field _must_ be populated, the grey text is _not_ a default value! If nothing is entered in the **Database Name** field then clicking the `Submit` button will generate an error page **Cannot select database** with a somewhat misleading message "The database server could be connected to ... but the database could not be selected." If this error message is encountered, go back to the previous page and check that the **Database Name** field is correctly populated.

> Once you've provided all of the relevant details, WordPress will tell you that it does not have permission to write `wp-config.php` itself. This is good, as allowing your web server to write config files itself is dangerous.
>
> > #### Tip
> >
> > You may be wondering why allowing a web server to write its own config files is dangerous. Open-source tools may have unknown bugs and exploits that allow an attacker to save files to disk on your server. Once there's a file on your disk, they can execute it to try to compromise your server. If your web server cannot write files at all, you are not vulnerable to this kind of attack.
>
> Instead of allowing WordPress to write `wp-config.php` for you, you're going to copy the config file and have Ansible install it for you.

An empty WordPress configuration file `provisioning/templates/wordpress/wp-config.php` has been provided for you. Edit this file and paste the configuration displayed by WordPress in the browser, i.e. the **Configuration rules for wp-config.php** text, into the `wp-config.php` file. Then, add the following task to copy the `wp-config.php` file to the correct place on the virtual machine:

```yaml
    - name: Create wp-config
      ansible.builtin.template:
        src: templates/wordpress/wp-config.php
        dest: /var/www/book.example.com/wp-config.php
```

> After adding this task, run Ansible again by running the `vagrant provision` command in your terminal.
>
> When you run Ansible, you may get an error message similar to the following :

```task
AnsibleError: ERROR! template error while templating string
```

> If you get this error message, take a look at the contents your `wp-config.php` file. Do you see any place that has either `{{` or `}}` in a string? Unfortunately, WordPress can generate this string as part of its secret keys. However, as you're using Ansible's template module, those characters have a special meaning. If your `wp-config.php` file contains them, feel free to edit the file and change them to any other character.
>
> Once Ansible has run successfully, go back to your web browser and click "Run the install." It will ask you a few questions.

The **Welcome** page asks for **Site Title**, **Username**, **Password** (pre-generated) and **Your Email**. All fields are required. Please ensure you have the login credentials saved in a password wallet for access to the WordPress site later.

> Answer these questions and click on "Install WordPress."

A **Success!** page should be displayed with text "WordPress has been installed. Thank you, and enjoy!"

> If you visit <http://192.168.33.20> now in your browser, you should see a WordPress install up and running with a "Hello World" post waiting to greet you.

#### Change the WordPress Theme to **Twenty Twenty-Two**

The default WordPress theme **Twenty Twenty-Four** is &mdash; confusingly &mdash; a sample page about an architectural firm. This is not really what we want and it's easy &mdash; if irksome &mdash; to change by visiting the WordPress admin page <http://192.168.33.20/wp-admin> (login with the credentials entered above), hovering the pointer over **Appearance** in the left sidebar, selecting **Themes** and activating the **Twenty Twenty-Two** theme.

> ### Making a Backup
>
> If you were to destroy your environment right now and re-provision it, you would be 90 percent of the way to a WordPress install. You would end up at that final screen where you need to provide details about your website. All of that information is stored in the database, so let's make a backup and have Ansible automatically import it .
>
> Log in to the environment with `vagrant ssh` and run the following commands to create a backup SQL file to be used by your Playbook:

```text
sudo mysqldump wordpress > /vagrant/provisioning/files/wp-database.sql
```

then exit from the shh session with `exit` or `Ctrl-d`.

> The last step is to write a task to import this backup into your database. You need to do a little extra work to make sure that you don't overwrite databases that already exist. You wouldn't want to replace production databases with your development backup, would you?
>
> We're going to use a new feature now, `ignore_errors`. Usually, when a command fails with a non-zero exit code, Ansible throws the error back to you. Using `ignore_errors` on a command tells Ansible that it's OK for that command to fail:

```yaml
    - name: Check for existing database
      ansible.builtin.command: mysql -u root wordpress -e "SELECT ID FROM wordpress.wp_users LIMIT 1;"
      register: db_exist
      ignore_errors: true
```

> This tries to select the first user from your WordPress database. This will fail if the database doesn't exist, which is your trigger to restore the database. You store the return value in `db_exist` for use in later tasks. If you need to import the database, you'll need to copy your database to the remote environment before you import it, so you will need two tasks to perform the import :

```yaml
    - name: Copy WordPress DB
      ansible.builtin.copy:
        src: files/wp-database.sql
        dest: /tmp/wp-database.sql
      when: db_exist.rc > 0
    - name: Import WordPress DB
      community.mysql.mysql_db:
        target: /tmp/wp-database.sql
        state: import
        name: wordpress
      when: db_exist.rc > 0
```

> Make sure to add these tasks to your playbook now. You only want to copy and import the database when `db_exist.rc` is greater than 0. `rc` stands for return code, and it is always zero when things are successful. It can be a number of values when things fail, but is generally set to 1. If you run Ansible now, you should see that these tasks are skipped, as your database already exists.
>
> ### Making It Idempotent
>
> If you run `vagrant provision` one more time, you'll notice that you have a task that says `changed` every time it runs. This isn't ideal, as it could trigger handlers or have other unintended side effects. You want your playbooks to say "OK" or "skipped" for every task when you look at the output of your playbook run.
>
> The task that always says `changed` is the `command` that you run to check if the database exists. The command module always reports that it changed something, as you don't know what the command actually does. Fortunately, you can suppress that using `changed_when`. `changed_when` is a field that controls whether Ansible thinks that a task performed an action that made a change or not. If the expression provided evaluates to `true`, Ansible will record that a change was made and trigger any handlers that need to run. If it evaluates to `false`, Ansible will record that no change was made, and no handlers will be triggered.
>
> Here's a simple example of how you can use `changed_when`. List out the contents of the `/tmp` directory and if see the word "`wordpress`" occurs anywhere in the output. If so, Ansible will report that the task changed something.

```yaml
    - name: Example changed_when
      ansible.builtin.command: ls /tmp
      register: demo
      changed_when: '"wordpress" in demo.stdout'
```

> If the text "`wordpress`" is not found in the command's output, Ansible will report that the task did not change anything, showing OK in the output.
>
> Ansible checks if the expression evaluates to `false` to decide if a task changed anything. As you never want the command that checks if the database exists to return "changed," you can specify `changed_when: false` to make it always return as OK.
>
> If you edit the task that checks the database so that it looks like the following, your playbook will be fully idempotent again:

```yaml
    - name: Check for existing database
      ansible.builtin.command: mysql -u root wordpress -e "SELECT ID FROM wordpress.wp_users LIMIT 1;"
      register: db_exist
      ignore_errors: true
      changed_when: false
```

> At this point, you can run `vagrant destroy` and then `vagrant up` to destroy your environment and spin it up as an empty box. Ansible will run and automatically provision your WordPress install for you. It may take a few minutes, as it's installing all of your dependencies as well as configuring WordPress.
>
> ### Summary
>
> Congratulations! You just automated an entire WordPress installation using Ansible. You've built up a fairly complex playbook step by step using several different modules to accomplish the tasks you needed. You installed and configured nginx and MySQL, as well as downloaded or copied WordPress onto your remote machine. Each time you encountered something that you had to do by hand, you looked into where that information was being persisted and added some tasks to your playbook to automate it in the future.
>
> Along the way, you learned about making your playbook idempotent when using the `command` module thanks to options such as `ignore_errors` and `changed_when`. Making your playbooks idempotent is an important part of managing your infrastructure with Ansible, so learning how to work with the `command` module in this way is very important.
>
> Although you've achieved a lot in this chapter, you may have noticed that as you added more and more to this playbook, it became harder to work with. In the next chapter, you'll take a look at roles, a concept that lets you break your playbooks up into distinct, reusable components, which you can piece together to deploy an application.

## Chapter 4 _Ansible Roles_

### Notes for Chapter 4

There are no significant changes required to this chapter, so Chapter 4 is not reproduced in this document. The only points to note are:

1. You should use _your_ username when decomposing the monolithic Playbook into roles, i.e. instead of `mheap.php`, `mheap.nginx`, etc. you should use `<username>.php`, `<username>.nginx`, etc.
1. `ansible-lint` will complain about `role-name` rule violations. If you are seeking to meet **Completion** or **Challenge** requirements then in this an instance where you are allowed to create and populate a `.ansible-lint` file at the top level of the repository with an entry to skip this rule. The output from `ansible-lint` gives excellent guidance on how to configure a skip rule.

You are requested to complete Chapter 4 as part of the assignment, please refer to the original text of the book for instructions and explanations.

---
