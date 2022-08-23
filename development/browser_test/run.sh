#!/usr/bin/env bash

echo "running \"codecept build\" from run.sh"

/data/codecept build
# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

# Run apache in foreground
apache2ctl -D FOREGROUND