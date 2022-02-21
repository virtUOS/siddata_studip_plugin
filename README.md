# Siddata-Stud.IP-Plugin

Date: Febuary 10, 2022

Authors: Niklas Dettmer <ndettmer@uos.de>, Dennis Benz <dbenz@uos.de>, Sebastian Osada <sebastian.osada@uni-osnabrueck.de>

The Siddata Stud.IP Plugin is part of the Siddata research project. Further information can be found at [www.siddata.de](http://www.siddata.de).
This plugin works as a client for providing Siddata functionalities in the learning management system (LMS) Stud.IP.
For this purpose it uses the Siddata backend API provided by a central federated web service. 

## Setup
The plugin is installed and used within Stud.IP (minimum version is 4.2).
1. Clone the repository.
2. Build installation archive: `php build.php`
3. Install the plugin in your Stud.IP:
   1. Login with root user.
   2. Navigate to /dispatch.php/admin/plugin.
   3. Install the plugin archive via the drag-and-drop field in the sidebar.  
   4. Activate the plugin.
4. Configure the Plugin:
   1. Navigate to /dispatch.php/admin/configuration/configuration and expand "SIDDATA".
   2. At least set the following fields like so:
      1. "SIDDATA_Brain_URL": URL of your Siddata backend API.
      2. "SIDDATA_IV" and "SIDDATA_KEY": 16 character keys provided by your backend host. 
      3. "SIDDATA_api_key": API key provided by your backend host.
      4. "SIDDATA_origin": Identification of your Stud.IP instance that has been defined in your backend. 
5. Start the cronjob once manually in `/dispatch.php/admin/cronjobs/tasks` and make sure your Cronjob scheduler is set up correctly. 

### For developers (using Linux): 
1. There are two common ways of running Stud.IP:
   1. Use the [docker image](https://hub.docker.com/r/studip/studip)
   2. Run [Stud.IP natively](https://hilfe.studip.de/admin/Admins/Installationsanleitung) on your machine
2. Developing on-the-fly:
   1. Grant write and read permissions to the plugin folder: (Linux) `sudo chmod -R 777 <Stud.IP directory>/public/plugins_packages/virtUOS/`
   2. Paste the hidden `.git` directory to `<Stud.IP directory>/public/plugins_packages/virtUOS/SiddataPlugin/`
   3. Make git ignore the file mode: `git config core.fileMode false`
   4. Restore missing files which are not included in the installation archive: `git stash & git stash clear`
