#!/bin/bash
# Part of Zoph 0.5
# This script attempts to make a hierarchical structure out of your 
# places database. 
# It is not supported and you should use it at your own risk. 
# Make a backup before you start.

db_name="zoph"
db_user="zoph_rw"
db_pass="pass"
db_prefix="zoph_"
db_host="localhost"

#---
mysql="mysql -u${db_user} -p${db_pass} -h${db_host} ${db_name} -Bs -e "
mysql_user_output="mysql -u${db_user} -p${db_pass} -h${db_host} ${db_name} -e "

function askcont {
   echo "Do you want to continue?"
   read answer
   case $answer in
   [yY])
   ;;
   *)
       exit
   ;;
   esac
}

doubles=`$mysql "select title, count(*) as number from zoph_places group by title having number > 1"`

if [ "$doubles" != "" ]
then
   echo "There seem to be some doubles in your location list:"
   $mysql_user_output "select title, count(*) as number from zoph_places group by title having number > 1 order by title;"
   askcont
fi

echo "Please check this list of countries for any unmeant doubles,"
echo "such as \"Netherlands\" and \"NL\"."
$mysql_user_output "select country, count(photo_id) as \"number of photos\" 
   from ${db_prefix}places as pl join ${db_prefix}photos as ph 
   on pl.place_id=ph.location_id group by country order by country;"
askcont

echo "Please check this list of states for any unmeant doubles,"
echo "such as \"Alberta\" and \"AB\"."
$mysql_user_output "select state, count(photo_id) as \"number of photos\" 
   from ${db_prefix}places as pl join ${db_prefix}photos as ph 
   on pl.place_id=ph.location_id group by state order by state;"
askcont

echo "Please check this list of cities for any unmeant doubles,"
echo "such as \"New York\" and \"New York City\"."
$mysql_user_output "select city, count(photo_id) as \"number of photos\" 
   from ${db_prefix}places as pl join ${db_prefix}photos as ph 
   on pl.place_id=ph.location_id group by city order by city;"
askcont

$mysql "select distinctrow country from "$db_prefix"places" | while read country
do
  if [ "x$country" != "xNULL" ] && [ "x$country" != "x" ]
  then
      echo "Found country: " $country
      places=`$mysql "select place_id from ${db_prefix}places where country=\"$country\" and title!=\"$country\""`;
      key=`$mysql "insert into "$db_prefix"places (parent_place_id, title, country) VALUES (1,\"$country\",\"$country\"); select last_insert_id()"`

      for place in $places
      do
          $mysql "update "$db_prefix"places set parent_place_id="$key" where place_id="$place
      done
  fi
done


# Try to figure out states and provinces

$mysql "select distinctrow parent_place_id from ${db_prefix}places" | while read parent
do
    $mysql "select distinctrow state from ${db_prefix}places where parent_place_id=\"$parent\"" | while read state
    do
        if [ "x$state" != "xNULL" ] && [ "x$state" != "x" ]
        then
            echo "  state: " $state
            states=`$mysql "select place_id from ${db_prefix}places where parent_place_id=${parent} and state=\"$state\" and title != \"${state}\""`;
            if [ "x$state" != "x" ]
            then
                statekey=`$mysql "select place_id from ${db_prefix}places
                    where state=\"$state\" and parent_place_id=\"$state\"
                    limit 1"`
 
                if [ "x$statekey" = "x" ]
                then 
                    country=`$mysql "SELECT country FROM ${db_prefix}places
                        where place_id=$parent"`
                    statekey=`$mysql "INSERT INTO "$db_prefix"places 
                        (parent_place_id, title, state, country) 
                        VALUES ($parent,\"$state\",\"$state\", \"$country\"); 
                        SELECT last_insert_id()"`
                fi
                for place in $states
                do
                    $mysql "update "$db_prefix"places set parent_place_id="$statekey" where place_id="$place
                done
            fi
        fi
    done
done


$mysql "select distinctrow parent_place_id from ${db_prefix}places" | while read parent
do
    $mysql "select distinctrow city from ${db_prefix}places where parent_place_id=\"$parent\"" | while read city
    do
        if [ "x$city" != "xNULL" ] && [ "x$city" != "x" ]
        then
            echo "    city: " $city
            cities=`$mysql "select place_id from ${db_prefix}places where parent_place_id=${parent} and city=\"$city\" and title != \"${city}\""`;
            if [ "x$cities" != "x" ]
            then
                citykey=`$mysql "select place_id from ${db_prefix}places
                    where city=\"$city\" and parent_place_id=\"$parent\" 
                    and title=\"$city\"
                    limit 1"`
 
                if [ "x$citykey" = "x" ]
                then
                    state=`$mysql "SELECT state FROM ${db_prefix}places
                        where place_id=$parent"`
                    country=`$mysql "SELECT country FROM ${db_prefix}places
                        where place_id=$parent"`
                    
                    citykey=`$mysql "INSERT INTO "$db_prefix"places 
                        (parent_place_id, title, city, state, country) 
                        VALUES ($parent,\"$city\",\"$city\", 
                        \"$state\", \"$country\"); 
                        SELECT last_insert_id()"`
                fi
                for place in $cities
                do
                    $mysql "update "$db_prefix"places set parent_place_id="$citykey" where place_id=$place and place_id!=$citykey"
                done
            fi
        fi
    done
done
