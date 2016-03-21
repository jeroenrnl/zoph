#!/bin/sh

# find files that are *not* in mid or thumbs
# and do *not* start with orig_
files="$(find . \( -iname '*.jpg' -o -iname '*.jpeg' \) -a ! -name 'orig_*' -a ! \( -path '/mid' -o -path '/thumb' \))";
echo "$files" | while read filename_full; do
  dirname=$(dirname "$filename_full");
  filename=$(basename "$filename_full");
#  extension="${filename##*.}";
#  filename="${filename%.*}";

  #
  # thumb
  #

  # check if a same named file exists in thumb,
  # but with a different case
  dirname_thumb="${dirname}/thumb";
  filename_thumb="thumb_${filename}";

  if [ -d "${dirname_thumb}" ]; then
    filename_full_thumb=$(find "${dirname_thumb}" -iname "${filename_thumb}");
    filename_thumb=$(basename "$filename_full_thumb");

    if [ "${filename_thumb}_NOTFOUND" != "_NOTFOUND" ]; then
      # check if the two files have different cases
      if [ "$filename_thumb" != "thumb_${filename}" ]; then
         filename_full_thumb_new="${dirname_thumb}/thumb_${filename}";

         echo "THUMB:";
         echo Found \"$filename_full\" and \"$filename_full_thumb\";
         echo Will rename \"$filename_full_thumb\" to \"$filename_full_thumb_new\";
         mv "$filename_full_thumb" "$filename_full_thumb_new";
      fi;
    fi;
  fi;

  #
  # mid
  #

  # check if a same named file exists in mid,
  # but with a different case
  dirname_mid="${dirname}/mid";
  filename_mid="mid_${filename}";

  if [ -d "${dirname}/mid" ]; then
    filename_full_mid=$(find "${dirname_mid}" -iname "${filename_mid}");
    filename_mid=$(basename "$filename_full_mid");

    if [ "${filename_mid}_NOTFOUND" != "_NOTFOUND" ]; then
      # check if the two files have different cases
      if [ "$filename_mid" != "mid_${filename}" ]; then
         filename_full_mid_new="${dirname_mid}/mid_$filename";

         echo "MID:"
         echo Found \"$filename_full\" and \"$filename_full_mid\";
         echo Will rename \"$filename_full_mid\" to \"$filename_full_mid_new\";
         mv "$filename_full_mid" "$filename_full_mid_new";
      fi;
    fi;
  fi;
done;
