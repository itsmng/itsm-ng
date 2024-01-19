#!/usr/bin/env bash

for lang in `find ../locales -name "*.po" -type f -printf "%f\n"`
do
  echo "Updating $lang";
  msgmerge -U ../locales/$lang ../locales/glpi.pot
done
