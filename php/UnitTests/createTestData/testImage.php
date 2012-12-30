<?php
/*
 * This class creates images to use as photos in Zoph's unittests 
 *
 * This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */

class testImage {

    private $name="";
    private $albums=array();
    private $categories=array();
    private $people=array();
    private $photographer="";
    private $location="";

    public function setName($name) {
        $this->name=$name;
    }

    private function getName() {
        return $this->name;
    }

    public function addToAlbum($album) {
        if(!in_array($album, $this->albums)) {
            $this->albums[]=$album;
        }
    }

    private function getAlbumCount() {
        return sizeof($this->albums);
    }
    
    public function addToCategory($cat) {
        if(!in_array($cat, $this->categories)) {
            $this->categories[]=$cat;
        }
    }
    private function getCategoryCount() {
        return sizeof($this->categories);
    }
    

    public function addPerson($pers) {
        $this->people[]=$pers;
    }
    private function getPersonCount() {
        return sizeof($this->people);
    }

    public function setPhotographer($ph) {
        $this->photographer=$ph;
    }
    public function setLocation($loc) {
        $this->location=$loc;
    }

    public function writeImage() {
        $locations=testData::getLocations();
        $albums=testData::getAlbums();
        $categories=testData::getCategories();
        $people=testData::getPeople();

        $colourleft=new ImagickPixel();
        $colourright=new ImagickPixel();
        $text=array();
        $rtext=array();

        if($this->getCategoryCount() > 0) {
            $colourleft->setColor($categories[$this->categories[0]][1]);
            $colourright->setColor($categories[$this->categories[1]][1]);
            $text[]="Categories:";
            foreach($this->categories as $i=>$cat) {
                $text[]="   " . $this->categories[$i] . ": " . $categories[$this->categories[$i]][1];
            }
        } else {
            $colour->setColor("white");
        }
        
        if($this->getAlbumCount() > 0) {
            $text[]="";
            $text[]="Albums:";
            foreach($this->albums as $i=>$alb) {
                $text[]="   " . $this->albums[$i] . ": " . $albums[$this->albums[$i]][1];
            }
        }
        if($this->location) {
            $rtext[]="Location:";
            $rtext[]="   " . $this->location . ": " . $locations[$this->location][1];
        }
        
        if($this->getPersonCount() > 0) {
            $rtext[]="";
            $rtext[]="People:";
            foreach($this->people as $i=>$person) {
                $rtext[]="   " . $this->people[$i] . ": " . $people[$this->people[$i]];
            }
        }

        if($this->photographer) {
            $rtext[]="";
            $rtext[]="Photographer:";
            $rtext[]="   " . $this->photographer . ": " . $people[$this->photographer];
        }


        $textcolour=new ImagickDraw();
        $textcolour->setFillColor("black");
        $textcolour->setFontsize(20);
        
        $image=new Imagick();
        $image->newImage(600,400, $colourleft);

        $draw=new ImagickDraw();
        $draw->setFillColor($colourright);
        $draw->rectangle(300,0,600,400);

        $image->drawImage($draw);


        foreach($text as $i=>$line) {
            $image->annotateImage($textcolour, 50, 50 + 25 * $i, 0, $line);
        }
        foreach($rtext as $i=>$line) {
            $image->annotateImage($textcolour, 350, 50 + 25 * $i, 0, $line);
        }
            
            
        $image->writeImage(conf::get("path.images") . "/" . $this->getName());
        $image->destroy();
        unset($image);
    }
}
?>
