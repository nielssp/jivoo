#!/bin/bash
apigen --source app \
  --destination doc \
  --exclue app/templates/* \
  --title PeanutCMS \
  --deprecated yes \
  --todo yes \
  --report log/apigen.report.log \
