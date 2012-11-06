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
require_once(__DIR__ . DS . "getid3" . DS . "getid3" . DS . "getid3.php");

class MusicFolder {

    // the music tag; see MusicTag class
    // i.e. represents the {music...}/some/path{/music} for this folder
    private $musicTag;

    public function __construct(MusicTag $musicTag) {
        $this->musicTag = $musicTag;
    }

    public function getMusicItems($ascending, $count, $offset = 0) {
        $files = $this->getSortedFiles($ascending);
        $upper = min($offset + $count, count($files));
        return $this->getMusicItemsBetweenBoundaries($files, $offset, $upper);
    }

    public function isExists() {
        return JFolder::exists($this->getFileBasePath());
    }

    public function getUrlBasePath() {
        $siteUrl = JURI :: base();
        if (substr($siteUrl, -1) == "/") {
            $siteUrl = substr($siteUrl, 0, -1);
        }
        return $siteUrl . "/" . $this->musicTag->getPathTrail();
    }

    public function getFileBasePath() {
        return JPATH_SITE . DS . $this->musicTag->getPathTrail();
    }

    private function getSortedFiles($ascending) {
        $files = JFolder::files($this->getFileBasePath());
        if ($ascending) {
            sort($files, SORT_STRING);
        } else {
            rsort($files, SORT_STRING);
        }
        return $files;
    }

    private function getMusicItemsBetweenBoundaries($files, $lower, $upper) {
        $musicItems = array();
        for ($i = $lower; $i < $upper; $i++) {
            $file = $files[$i];
            $musicItems[] = $this->getMusicItemForFilePathName($file);
        }
        return $musicItems;
    }

    private function getMusicItemForFilePathName($file) {
        $getID3 = new getID3;
        $getID3->encoding = "UTF-8";
        $filePathName = $this->getFileBasePath() . DS . $file;
        $ThisFileInfo = $getID3->analyze($filePathName);
        getid3_lib::CopyTagsToComments($ThisFileInfo);
        require_once(__DIR__ . DS . "MusicItem.php");
        return new MusicItem($this, $file, $ThisFileInfo);
    }

}
