#!/bin/sh
SERVICE=$1
if ps ax | grep -v grep | grep -v $0 | grep $SERVICE > /dev/null
then
    touch /home/twigavid/public_html/.del || true
else
    rm -f /home/twigavid/public_html/.del || true
fi