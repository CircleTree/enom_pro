#!/usr/bin/python

GIT_COMMIT_FIELDS = ["id", "date", "message"]
GIT_LOG_FORMAT = ['%H', '%ad', '%s']
GIT_LOG_FORMAT = '%x1f'.join(GIT_LOG_FORMAT) + '%x1e'
import subprocess

p = subprocess.Popen("git log -n 15 --format='%s' " % GIT_LOG_FORMAT, shell=True, stdout=subprocess.PIPE)
(log, _) = p.communicate()
log = log.strip('\n\x1e').split("\x1e")
log = [row.strip().split("\x1f") for row in log]
log = [dict(zip(GIT_COMMIT_FIELDS, row)) for row in log]

from pprint import pprint
pprint(log)