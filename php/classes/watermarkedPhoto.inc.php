<?php
/**
 * A class representing a watermarked photo
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
 */

/**
 * A class representing a watermarked photo
 * This is a photo with a "watermark" superimposed over it
 * usually to prevent unauthorized use the photo
 *
 * @package Zoph
 * @author Jeroen Roos
 */
class watermarkedPhoto extends photo {
    /**
     * Display the watermarked image
     * @param string type of image to display mid, thumb or null for full-sized
     * @return array Return an array that contains:
     *               array headers: the headers
     *               string jpeg: the jpeg file
     * @todo only supports JPEG currently, should support more filetypes
     */
    public function display($type=null) {
        $headers=array();
        // Only fullsize images are (currently) watermarked
        if(empty($type)) {
            $watermark_file = conf::get("path.images") . "/" . conf::get("watermark.file");
            if (file_exists($watermark_file)) {
                $name = $this->get("name");
                $image_path = conf::get("path.images") . "/" . $this->get("path") . "/" . $name;
                $image=imagecreatefromjpeg($image_path);
                $image=$this->watermark($image, $watermark_file, conf::get("watermark.pos.x"), conf::get("watermark.pos.y"), conf::get("watermark.transparency"));
                ob_start();
                    imagejpeg($image);
                    imagedestroy($image);
                $jpeg=ob_get_clean();
                $headers["Content-Length"]=strlen($jpeg);
                $headers["Content-Disposition"]="inline; filename=" . $name;
                // Return current time as last modified time 
                // this is debatable, we could also send the file time as last modified
                $headers["Last-Modified"]=gmdate("D, d M Y H:i:s") . ' GMT';
                $headers["Content-type"]="image/jpeg";

                return array($headers, $jpeg);

            }
        }
        return parent::display($type);
    }

    /**
     * Watermark the photo
     * @param imageresource photo
     * @param string GIF image to be used as watermark
     * @param string position horizontally (center, left or right)
     * @param string position vertically (center, top or bottom)
     * @param int transparency (0 = invisible, 100 = no transparency)
     * @return imageresource watermarked photo
     */
    private function watermark($orig, $watermark, $positionX = "center", $positionY = "center", $transparency = 50) {

        $wm=imagecreatefromgif($watermark);
        
        $width_orig=ImageSX($orig);
        $height_orig=ImageSY($orig);

        $width_wm=ImageSX($wm);
        $height_wm=ImageSY($wm);

        switch ($positionX) {
        case "left":
            $destX = 5;
            break;
        case "right":
            $destX = $width_orig - $width_wm - 5;
            break;
        default:
            $destX = ($width_orig / 2) - ($width_wm / 2);
            break;
        }

        switch ($positionY) {
        case "top":
            $destY = 5;
            break;
        case "bottom":
            $destY = $height_orig - $height_wm - 5;
            break;
        default:
            $destY = ($height_orig / 2) - ($height_wm / 2);
            break;
        }
        ImageCopyMerge($orig, $wm, $destX, $destY, 0, 0, $width_wm, $height_wm, $transparency);
        imagedestroy($wm);
        return $orig;
    }


}
?>
