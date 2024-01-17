#!/usr/bin/env bash

files=()

# Find all .po files and add them to the array
while IFS= read -r -d '' file; do
  files+=("$file")
done < <(find ../locales -name '*.po' -type f -print0)

# Update all .po files
for file in "${files[@]}"; do
  msgmerge --previous --update $file ../locales/glpi.pot
done

# Remove temporary files
find ../locales -name '*.po~' -type f -delete
