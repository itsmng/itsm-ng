#!/usr/bin/env bash

find ../locales -name "*.po" | while read file; do
  lang=$(basename $file .po)
  echo "Updating $lang";
  msgfmt $file -o ../locales/$lang.mo
done
