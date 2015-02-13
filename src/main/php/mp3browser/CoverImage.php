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
 * Copyright 2012-'13 Totaal Software (www.totaalsoftware.com).
 */
defined("_JEXEC") or die("Restricted access");

jimport('joomla.environment.browser');

class CoverImage {

    private $data;
    private $encoding;
    private $mimeType;

    public function __construct($getId3FileInfo) {
        if (isset($getId3FileInfo['id3v2']['APIC'][0]['data'])) {
            return $this->setFieldsFromImage($getId3FileInfo['id3v2']['APIC'][0]);
        } else if (isset($getId3FileInfo['id3v2']['PIC'][0]['data'])) {
            return $this->setFieldsFromImage($getId3FileInfo['id3v2']['PIC'][0]);
        }
    }

    private function setFieldsFromImage($image) {
        $this->data = $image['data'];
        $this->encoding = $image['encoding'];
        $this->height = $image['image_height'];
        $this->mimeType = $image['image_mime'];
        $this->width = $image['image_width'];
    }

    public function getSrc() {
        if ($this->isBrowserSupportedLimited()) {
            return $this->constructSrcWith32kLimit();
        }
        return $this->constructSrcFromBase64(base64_encode($this->data), $this->mimeType);
    }

    /**
     * This constructs an image embedded in a src tag (data URI) with a size
     * limit of 32kb. As this is the size limitation of IE8. This may well be
     * overly resource intensive, but it's the best result I can think of.
     * @return type constructed src attribute for img tag
     */
    private function constructSrcWith32kLimit() {
        $original = imagecreatefromstring($this->data);
        $correction = 1;
        do {
            $newWidth = round($this->width * $correction);
            $newHeight = round($this->height * $correction);
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            $var = imagecopyresized($newImage, $original, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);
            ob_start();
            imagejpeg($newImage);
            $contents = ob_get_contents();
            ob_end_clean();
            $base64newImage = base64_encode($contents);
            $correction = $correction * 0.8;
        } while (strlen($base64newImage) > (32 * 1024 /* = 32kb */));
        return $this->constructSrcFromBase64($base64newImage, "image/jpeg");
    }

    public static function isBrowserSupported() {
        $browser = JBrowser::getInstance();
        switch ($browser->getBrowser()) {
            case "msie":
                return $browser->getMajor() >= 8;
            case "opera":
                return $browser->getMajor() >= 7;
            default:
                return true;
        }
    }

    private function isBrowserSupportedLimited() {
        $browser = JBrowser::getInstance();
        return $browser->getBrowser() == "msie" && $browser->getMajor() == 8;
    }

    private function constructSrcFromBase64($base64data, $mimeType) {
        return "data:" . $mimeType . ";base64," . $base64data;
    }

    public static function hasCover($getId3FileInfo) {
        return isset($getId3FileInfo['id3v2']['APIC'][0]['data'])
                || isset($getId3FileInfo['id3v2']['PIC'][0]['data']);
    }

}