#!/bin/bash
apigen --source app \
  --destination doc \
  --exclude "app/templates/*" \
  --title PeanutCMS \
  --deprecated yes \
  --todo yes \
  --report log/apigen.report.log \
  2> log/apigen.error.log
