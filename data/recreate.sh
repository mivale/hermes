#!/bin/sh
#
rm -f new.db && sqlite3 new.db < hermes.sql && mv new.db hermes.db && chmod 666 hermes.db