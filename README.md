# Reddingsmonitor
The Reddingsmonitor is a project set up in collaboration with the Zandvoort rescue brigade.

## Installing upload script on Windows

### Install WSL 2.
* Open Apps & Features
* Open Programs and Features
* Open Turn Windows features on or off menu
* Enable Windows Subsystem for Linux
* Press OK and reboot your system
* Open PowerShell (Run as Administrator)
* Set WSL 2 as default
* wsl ```--set-default-version 2```
* Open the Microsoft Store Page and install Debian
* Open Debian by pressing CNTRL + R, type Debian and hit enter.
* Follow install instructions
* Close installation window
* Run package manager on WSL. ```sudo apt-get update && sudo apt-get dist-upgrade```
* Install extra packages on WSL. ```sudo apt-get install php-cli php-curl php-dom```
* Deploy https://github.com/KvanSteijn/reddingsmonitor in a folder on your system.
* We need to create a new logon task in the windows task scheduler so that upload will start when at logon.
* Open the windows task scheduler. Not task manager, but task scheduler.
* In the top right click 'create task'.
* Fill in a task name. For example 'Reddingsmonitor upload'.
* At the top, click on the tab 'Triggers'.
* Create a new trigger, and in the dropdown menu at the top begin the task 'At log on'. Click ok.
* At the top, click on the tab 'Actions'.
* In the windows task scheduler, add a new logon task. With script ```wsl``` and arguments. The arguments need some user adjustments before you can click on save.
```shell
bash -c "nohup php /mnt/c/Users/{NAME}/Desktop/Reddingsmonitor/bin/upload.php --url {URL} --secretKey {KEY} --file snaphot.kml > /dev/null 2>&1 &"
```
* Replace the {NAME} path with the path to upload.php on your pc. Make sure the path starts with /mnt/c/ and not C:.
* Replace the {URL}, {KEY} with the details that is given from Reddingsmonitor team.