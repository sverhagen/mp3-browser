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
        return $this->musicFolder->getUrlBasePath() . "/" . $this->fileName;
    }

    private function getCover() {
        if (isset($this->getId3FileInfo['id3v2']['APIC'][0]['data'])) {
            return $this->getId3FileInfo['id3v2']['APIC'][0]['data'];
        } else if (isset($this->getId3FileInfo['id3v2']['PIC'][0]['data'])) {
            return $this->getId3FileInfo['id3v2']['PIC'][0]['data'];
        }
        return NULL;
    }

    private function getCoverMime() {
        if (isset($this->getId3FileInfo['id3v2']['APIC'][0]['data'])) {
            return $this->getId3FileInfo['id3v2']['APIC'][0]['image_mime'];
        } else if (isset($this->getId3FileInfo['id3v2']['PIC'][0]['data'])) {
            return $this->getId3FileInfo['id3v2']['PIC'][0]['image_mime'];
        }
        return NULL;
    }

    public function hasCover() {
        return isset($this->getId3FileInfo['id3v2']['APIC'][0]['data'])
                || isset($this->getId3FileInfo['id3v2']['PIC'][0]['data']);
    }

    /**
     * Get a string that points to the cover art image.
     * @return string null if no cover, otherwise src string that can be used in <img src="...">
     */
    public function getCoverSrc() {
        if ($this->hasCover()) {
            $cover = $this->getCover();
            $coverMime = $this->getCoverMime();
            return "data:" . $coverMime . ";base64," . base64_encode($cover);
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

}
