#!/usr/bin/env bash

for lang in `find ../locales -name "*.po" -type f -printf "%f\n"`
do
  echo "Updating $lang";
  msgattrib --no-fuzzy -o ../locales/$lang ../locales/$lang
  msgmerge -U --no-fuzzy-matching ../locales/$lang ../locales/glpi.pot
done

rm ../locales/*.po~
