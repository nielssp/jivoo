#!/bin/bash
apigen --source app \
  --source lib \
  --destination doc \
  --exclude "app/templates/*" \
  --title Jivoo \
  --deprecated yes \
  --todo yes \
  --report log/apigen.report.log \
  2> log/apigen.error.log
