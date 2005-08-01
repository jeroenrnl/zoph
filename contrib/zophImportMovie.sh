#!/bin/bash
# (c) giles@chaletpomme.com April 2004
# for oly c-750 videos

movie=$1

year=2005

echo "Adding movie $movie to Zoph database"
echo "Assuming year is $year - change in source"
sleep 3

# Work out dates, paths and filenames
filename=$(basename $movie)

month=$(echo $filename|sed "s/^[Pp]//"|head -c 1)
day=$(echo $filename|sed "s/^[pP]//"|head -c 3|tail -c 2)
case $month in
    a)
	month=10
	;;
    b)
	month=11
	;;
    c)
	month=12
	;;
esac
month=$(printf %02d $month)


# Paths:
basepath=/maxtor/photographs
path=olympus/$year.$month.$day
midsize=$basepath/$path/mid/mid_${filename%.MOV}.jpg
thumbsize=$basepath/$path/thumb/thumb_${filename%.MOV}.jpg


echo month is $month, day is $day
echo path is: $path
echo Midsize is $midsize
echo Thumbnail is $thumbsize

# EXIF-type stuff
frames=$(tcprobe -i $movie 2>/dev/null|tr ':,' '\n'|grep frames|tr ' ' '\n'|grep [0-9] ) >/dev/null
exposure=" $[ $frames / 15 ] seconds"
echo Video has $frames frames, i.e. $exposure seconds
size=$(find $movie -printf "%s")

has_audio=$(tcprobe -i $movie 2>/dev/null|grep "7875,8"|wc -l)
if [ $has_audio -eq 1 ] ; then
    comment="video with audio"
else
    comment="video"
fi

# 5 is the location_id for unknown video location

mysql -v -u giles -e "USE zoph ; INSERT INTO photos (name,path,date,exposure,comment,compression,size,location_id) VALUES (\"$filename\",\"$path\",\"$year-$month-$day\",\"$exposure\",\"$comment\",\"MJPEG\",$size,5);"

# Add to movie category.  This is category 11.
#photo_id=$(mysql -s -u giles -e "USE zoph ; SELECT photo_id FROM photos WHERE name=\"$filename\";"|grep -v photo_id|head -1)


# Ensure directory exists:
if [ ! -d $basepath/$path ] ; then
    echo Creating image directories
    mkdir $basepath/$path
    mkdir $basepath/$path/thumb
    mkdir $basepath/$path/mid
else
    echo Directories exist
fi



rm /tmp/grab*.ppm

step=$[ $frames / 25 ]
echo therefore step is $step

for grab in $(seq 0 24)
  do
  frame=$[ $grab * $step ]
  echo -n "$frame($grab) "
  transcode -i $movie -x auto,null -o /tmp/grab -y ppm -c $frame-$[ $frame + 1 ] 2>&1 > /dev/null 2>/dev/null || sleep 5
  mv /tmp/grab000000.ppm /tmp/grab$grab.ppm
  
done
echo

montage -geometry 96x72+0+0 -tile 5x5 -borderwidth 0 $( ls -1v --color=none /tmp/grab*.ppm) $midsize

#rm /tmp/grab*.ppm

echo Midsize written to $midsize





# Now making thumbnail
# We can only show four frames in the 120x90 thumbnail
step=$[ $frames / 4 ]
echo therefore step is $step

for grab in $(seq 0 3)
  do
  frame=$[ $grab * $step ]
  echo -n "$frame($grab) "
  transcode -i $movie -x auto,null -o /tmp/grab -y ppm -c $frame-$[ $frame + 1 ] 2>&1 > /dev/null 2>/dev/null || sleep 5
  mv /tmp/grab000000.ppm /tmp/grab$grab.ppm
  
done
echo

montage -geometry 60x45+0+0 -tile 2x2 -borderwidth 0 $( ls -1v --color=none /tmp/grab[0123].ppm) $thumbsize

mogrify -stroke yellow -pointsize 34 -draw "text 5,58 VIDEO" $thumbsize
if [ $has_audio -eq 1 ] ; then
    mogrify -stroke yellow -pointsize 20 -draw "text 50,70 audio" $thumbsize
fi
echo Thumbnail written to $thumbsize

rm /tmp/grab*.ppm

mv -v $movie $basepath/$path/

echo
echo
