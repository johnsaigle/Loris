#!/bin/bash

# Installs dependencies required for LORIS development on Ubuntu

os_distro=$(hostnamectl |awk -F: '/Operating System:/{print $2}'|cut -f2 -d ' ')
debian=("Debian" "Ubuntu")
if [[ ! " ${debian[*]} " =~ " $os_distro " ]]; then
    echo "Only Debian and Ubuntu are supported by $0."
    exit 0;
fi

# apache2 is not included here because a developer may use a different server
# config, such as the builtin PHP web server.
declare -a prereqs=(
"php"
"composer"
"nodejs" # TODO this should check for a version >= 8.0
)

# Verify prerequisites installed
for i in "${prereqs[@]}"
do
   # `command` is a cross-platform way to figure out whether these commands are
   # installed.
   command -v "$i" > /dev/null
   if [ ! "$?" -eq "0" ]; then
       echo "ERROR: the command '$i' does not appear to be installed on this system."
       echo "$i is required for development but cannot be installed by this script."
       echo "Please install $i and run this script again"
       exit 1
   else
       echo "Requirement $i appears to be satisfied"
   fi
done

# Last updated for LORIS version: 21.0.2
export PHP='7.2'
declare -a dependencies=(
"curl"
"wget"
"zip"
"unzip"
"php-json"
"make"
"software-properties-common"
"php-ast"
"php$PHP"
"php$PHP-mysql"
"php$PHP-xml"
"php$PHP-json"
"php$PHP-mbstring"
"php$PHP-gd"
"libapache2-mod-php$PHP"
)

echo "Adding 'ppa:ondrej/php' (needed for newer versions of PHP)...."
sudo add-apt-repository ppa:ondrej/php
echo "Updating apt package directory..."
sudo apt update > /dev/null

## Install all dependencies using sudo
for i in "${dependencies[@]}"
do
   echo "Installing dependency '$i' using apt...."
   sudo apt install -f -y "$i"
done

