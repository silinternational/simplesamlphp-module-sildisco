#!/usr/bin/env bash

if [[ "x" == "x$LOGENTRIES_KEY" ]]; then
    echo "Missing LOGENTRIES_KEY environment variable";
else
    # Set logentries key based on environment variable
    sed -i /etc/rsyslog.conf -e "s/LOGENTRIESKEY/${LOGENTRIES_KEY}/"
    # Start syslog
    rsyslogd
    sleep 10
fi

echo "Installing php-xdebug"
apt-get update -y
apt-get install -y php-xdebug
phpenmod xdebug

INI_FILE="/etc/php/7.0/apache2/php.ini"
echo "Configuring debugger in $INI_FILE"
echo "xdebug.remote_enable=1" >> $INI_FILE
echo "xdebug.remote_host=$XDEBUG_REMOTE_HOST" >> $INI_FILE

mkdir -p /data/vendor/simplesamlphp/simplesamlphp/modules/sildisco
touch /data/vendor/simplesamlphp/simplesamlphp/modules/sildisco/enable

php -r "require 'vendor/autoload.php'; echo(PHP_EOL . 'Aws\Sdk::VERSION = ' . Aws\Sdk::VERSION . PHP_EOL);"

# now the builtin run script can be started
/data/run.sh
