#!/usr/bin/env bash

echo "Empty run.sh"

codecept build

# Run apache in foreground
apache2ctl -D FOREGROUND