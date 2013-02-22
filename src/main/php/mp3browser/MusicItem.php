<?php

/**
 * This file is part of mp3 Browser.
 *
 * This is free software: you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation, either version 2 of the
 * License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License (V2) along with this. If not,
 * see <http://www.gnu.org/licenses/>.
 *
 * Previous copyright likely held by others such as Jon Hollis, Luke Collymore, as associated with
 * dotcomdevelopment.com.
 * Copyright 2012 Sander Verhagen (verhagen@sander.com).
 */
defined("_JEXEC") or die("Restricted access");

require_once(dirname(__FILE__) . DS . "CoverImage.php");

class MusicItem {

    private $musicFolder;
    private $fileName;
    private $getId3FileInfo;

    public function __construct($musicFolder, $fileName, $getId3FileInfo) {
        $this->musicFolder = $musicFolder;
        $this->fileName = $fileName;
        $this->getId3FileInfo = $getId3FileInfo;
    }

    public function getTitle() {
        if (isset($this->getId3FileInfo["comments"]["title"][0])) {
            return $this->getId3FileInfo["comments"]["title"][0];
        } else {
            // use file name as an alternative to title
            $lastDot = strrpos($this->fileName, ".");
            return substr($this->fileName, 0, $lastDot);
        }
    }

    public function getArtist() {
        if (isset($this->getId3FileInfo["comments"]["artist"][0])) {
            return $this->getId3FileInfo["comments"]["artist"][0];
        } else {
            return "";
        }
    }

    public function getUrl() {
        if (isset($this->getId3FileInfo["comments"]["url_user"][0])) {
            return $this->getId3FileInfo["comments"]["url_user"][0];
        } else {
            return "";
        }
    }

    public function getPlayTime() {
        if (isset($this->getId3FileInfo ["playtime_string"])) {
            return $this->getId3FileInfo ["playtime_string"] . " min";
        } else {
            return "";
        }
    }

    public function getFileSize() {
        $filePathName = $this->musicFolder->getFileBasePath() . DS . $this->fileName;
        $fileSize = ( filesize($filePathName) * .0009765625 ) * .0009765625;
        $fileSize = round($fileSize, 1);
        return $fileSize . " MB";
    }

    public function getUrlPath() {
        $urlPath = $this->musicFolder->getUrlBasePath() . "/" . $this->fileName;
        // need to encode non-ASCII
        return filter_var($urlPath, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);
    }

    public function hasCover() {
        return CoverImage::hasCover($this->getId3FileInfo);
    }

    /**
     * Get a string that points to the cover art image.
     * @return string null if no cover, otherwise src string that can be used in <img src="...">
     */
    public function getCover() {
        if ($this->hasCover()) {
            return new CoverImage($this->getId3FileInfo);
        }
        return NULL;
    }

    public function getComments() {
        if (isset($this->getId3FileInfo['id3v2']['comments']['comment'][0])) {
            return $this->getId3FileInfo['id3v2']['comments']['comment'][0];
        }
        return NULL;
    }

    public function getCopyright() {
        if (isset($this->getId3FileInfo['id3v2']['comments']['copyright_message'][0])) {
            return $this->getId3FileInfo['id3v2']['comments']['copyright_message'][0];
        }
        return NULL;
    }

    public function getCdataName() {
        $cdata = $this->fileName;
        $cdata = str_replace(" ", "_", $cdata);
        $cdata = preg_replace("/\.mp3$/", "", $cdata);
        return $cdata;
    }

}
