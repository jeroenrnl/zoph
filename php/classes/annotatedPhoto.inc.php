<?php
/**
 * A class representing an annotated photo
 * An annotated photo is a photo with information about the 
 * photo added to the image
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
 * @package Zoph
 * @author Jeroen Roos
 * @author Richard P. Childs
 */

class annotatedPhoto extends photo {

    /** The vars that are going to be displayed on the photo */
    private $vars=array();

   /**
    * Creates a jpeg photo with
    * text annotation at the bottom.
    *
    * Copyright 2003, Nixon P. Childs
    * @param string type of image to display mid, thumb or null for full-sized
    * @return array Return an array that contains:
    *               array headers: the headers
    *               string jpeg: the jpeg file
    * @throws photoException
    */
    public function display($type=null) {
        if($type=="full") {
            $type=null;
        }
        $headers=array();
        $vars=&$this->vars;
        if ($type == 'mid') {
            $font = 4;
            $padding = 2;
            $indent = 8;
        } else if (empty($type)) {
            $font = 5;
            $padding = 2;
            $indent = 8;
        } else {
            throw new photoException("Unknown type");
        }

        /* ********************************
         *  Read in original image.
         *  Need to do now so we know
         *  the width of the text lines.
         * ********************************/

        $image_path = conf::get("path.images") . $this->get("path");
        $name=$this->get("name");
        if (empty($type)) {
            $image_path .= "/" . $name;
        } else {
            $image_path .= "/" . $type . "/" . $type . "_" . $name;
        }

        $image_info = getimagesize($image_path);
        switch ($image_info[2]) {
            case 1:
                $orig_image = imagecreatefromgif($image_path);
                break;
            case 2:
                $orig_image = imagecreatefromjpeg($image_path);
                break;
            case 3:
                $orig_image = imagecreatefrompng($image_path);
                break;
            default:
                log::msg("Unsupported image type.", log::ERROR, log::IMG);
                return '';
        }

        $row = ImageSY($orig_image) + ($padding/2);
        $maxWidthPixels = ImageSX($orig_image) - (2 * $indent);
        $maxWidthChars = floor($maxWidthPixels / ImageFontWidth($font)) - 1;


        /* **********************************************
         *  Create Image
         *  In order to create the text area, we must
         *  first create the text and determine how much
         *  space it requires.
         *
         *  I tried implode;wordwrap;explode, but
         *  wordwrap doesn't respect \n's in the text.
         *  To complicate things, ImageString just
         *  renders \n as an upside-down Y.
         *
         *  So the current solution is a little awkward,
         *  but it works.  The only (known) problem is
         *  that wrapped lines don't have the same
         *  right margin as non-wrapped lines.  This is
         *  because wordwrap doesn't take into account
         *  the line separation string.
         * **********************************************/

        $count = 0;
        $final_array=array();
        if ($vars) {
            while (list($key, $val) = each($vars)) {
                $tmp_array = explode("\n", wordwrap($val, $maxWidthChars, "\n   "));
                while (list($key1, $val1) = each($tmp_array)) {
                    $final_array[$count++] = $val1;
                }
            }
        }

        $noted_image = ImageCreateTrueColor (ImageSX($orig_image), ImageSY($orig_image) + ((ImageFontHeight($font) + $padding) * $count));
        $white = ImageColorAllocate($noted_image, 255,255, 255);

        /* Use a light grey background to hide the jpeg artifacts caused by the sharp edges in text. */

        $offwhite = ImageColorAllocate($noted_image, 240,240, 240);
        ImageFill($noted_image, 0, ImageSY($orig_image) +1, $offwhite);
        $black = ImageColorAllocate($noted_image, 0, 0, 0);
        ImageColorTransparent($noted_image, $black);

        ImageCopy($noted_image, $orig_image, 0, 0, 0, 0, ImageSX($orig_image), ImageSY($orig_image));

        if ($final_array) {
            while (list($key, $val) = each($final_array)) {
                ImageString ($noted_image, $font, $indent, $row, $val, $black);
                $row += ImageFontHeight($font) + $padding;
            }
        }

        ob_start();
            imagejpeg($noted_image);
            imagedestroy($orig_image);
            imagedestroy($noted_image);
        $jpeg=ob_get_clean();

        $headers["Content-Length"]=strlen($jpeg);
        $headers["Content-Disposition"]="inline; filename=" . $name;
        // Return current time as last modified time 
        // this is debatable, we could also send the file time as last modified
        $headers["Last-Modified"]=gmdate("D, d M Y H:i:s") . ' GMT';
        $headers["Content-type"]="image/jpeg";

        return array($headers, $jpeg);


    }

    /**
     * Sets fields from the given array.  Can be used to set vars
     * directly from a GET or POST.
     * @param array vars to import into $this->vars;
     */
    public function setVars(array $vars) {
        reset($vars);
        while (list($key, $val) = each($vars)) {

            // ignore empty keys or values
            if (empty($key) || $val == "") { continue; }

            if (strcmp(Substr($key, strlen($key) - 3), "_cb") == 0) {

                /* *****************************************
                 *  Everthing else uses the checkbox name
                 *  as the "get" key.
                 * *****************************************/

                $real_key = Substr($key, 0, strlen($key) - 3);
                $real_val = $vars[$real_key];

                /* *****************************************
                 *  Have to handle title separately because
                 *  breadcrumbs.inc.php assumes title is
                 *  the page title.
                 * *****************************************/

                if ($real_key == "photo_title") {
                   $real_key = "title";
                }
                else if ($real_key == "extra") {
                   $real_key = $vars["extra_name"];
                }

                $this->vars[$real_key] = translate($real_key, 0) . ": " .  $real_val;
            }
        }
    }

    /**
     * Return the vars, so the can be re-used after a form POST
     */
    public function getVars() {
        return $this->vars;
    }
}
?>
