<?php
/**
 * This class stores relations between 2 photos.
 * This could be used store an original and a changed copy
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
 * @author Jeroen Roos
 * @package Zoph
 */

/**
 * This class stores relations between 2 photos.
 * This could be used store an original and a changed copy
 *
 * @author Jeroen Roos
 * @package Zoph
 */
class photoRelation extends zophTable {

    /** @var string The name of the database table */
    protected static $tableName="photo_relations";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("photo_id_1", "photo_id_2");
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set by the
                  db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="photo.php?photo_id=";

    /**
     * Create a new relation between two photos.
     * order of the photos is not important
     * @param photo first photo
     * @param photo second photo
     * @return photoRelation newly created photoRelation
     */
    public function __construct(photo $photo1, photo $photo2) {
        $this->set("photo_id_1", $photo1->getId());
        $this->set("photo_id_2", $photo2->getId());
    }

    /**
     * Get id
     * @return array ids
     */
    public function getId() {
        return array(
            "photo_id_1" => (int) $this->get("photo_id_1"),
            "photo_id_2" => (int) $this->get("photo_id_2")
        );
    }

    /**
     * Lookup in database.
     * Tries to look up in the database, first (photo_1, photo_2), then (photo_2, photo_1)
     * @return bool success or not
     */
    public function lookup() {
        if (!parent::lookup()) {
            $photoId1=$this->get("photo_id_1");
            $photoId2=$this->get("photo_id_2");

            $this->set("photo_id_1", $photoId2);
            $this->set("photo_id_2", $photoId1);

            return parent::lookup();
        } else {
            return true;
        }
    }

    /**
     * Get description.
     * Get description of the photo in the first param for the current relation
     * @param photo Photo to get description for
     * @throws RelationException if you try to lookup a photo that is not part of this relation
     * @return string description
     */
    public function getDesc(photo $photo) {
        if ($photo->getId() == $this->get("photo_id_1")) {
            return $this->get("desc_1");
        } else if ($photo->getId() == $this->get("photo_id_2")) {
            return $this->get("desc_2");
        } else {
            throw new RelationException("photo not in relation");
        }
    }

    /**
     * Set description.
     * Set description of the photo in the first param for the current relation
     * @param photo Photo to set description for
     * @param string description
     * @throws RelationException if you try to lookup a photo that is not part of this relation
     */
    public function setDesc(photo $photo, $desc) {
        if ($photo->getId() == $this->get("photo_id_1")) {
            $this->set("desc_1", $desc);
        } else if ($photo->getId() == $this->get("photo_id_2")) {
            $this->set("desc_2", $desc);
        } else {
            throw new RelationException("photo not in relation");
        }
    }

    /**
     * Define a relation between two photos, with descriptions.
     * Automatically creates new or updates existing relation
     * @param photo first photo
     * @param photo second photo
     * @param string description for first photo
     * @param string description for second photo
     */
    public static function defineRelation(photo $photo1, photo $photo2, $desc1, $desc2) {
        $rel=new photoRelation($photo1, $photo2);

        $exists=$rel->lookup();
        $rel->setDesc($photo1, $desc1);
        $rel->setDesc($photo2, $desc2);

        if ($exists===true) {
            $rel->update();
        } else {
            $rel->insert();
        }
    }

    /**
     * Get related photos
     * @param photo photo to get relations for
     * @return array of photos
     */
    public static function getRelated(photo $photo) {
        $qry=new select(array("pr" => "photo_relations"));
        $qry->addFunction(array("photo_id" => "photo_id_1"));
        $where=new clause("photo_id_2=:photoid2");
        $qry->addParam(new param(":photoid2", (int) $photo->getId(), PDO::PARAM_INT));
        $qry->where($where);

        $qry2=new select(array("pr" => "photo_relations"));
        $qry2->addFunction(array("photo_id" => "photo_id_2"));
        $where2=new clause("photo_id_1=:photoid1");
        $qry2->addParam(new param(":photoid1", (int) $photo->getId(), PDO::PARAM_INT));
        $qry2->where($where2);

        $qry->union($qry2);

        $related=photo::getRecordsFromQuery($qry);
        return $related;
    }

    /**
     * Get relation for 2 specific photos.
     * Order of photos is not important
     * @param photo first photo
     * @param photo second photo
     * @returns photoRelation|bool relation, if found or false
     */
    public static function getRelationForPhotos(photo $photo1, photo $photo2) {
        $rel=new photoRelation($photo1, $photo2);

        if (!$rel->lookup()) { return false; }

        return $rel;
    }

    /**
     * Get relation for 2 specific photos.
     * Returns description for SECOND photo.
     * @param photo first photo
     * @param photo second photo
     * @returns string description
     */
    public static function getDescForPhotos(photo $photo1, photo $photo2) {
        $rel=static::getRelationForPhotos($photo1, $photo2);
        if ($rel instanceof photoRelation) {
            return $rel->getDesc($photo2);
        }
    }



}

?>
