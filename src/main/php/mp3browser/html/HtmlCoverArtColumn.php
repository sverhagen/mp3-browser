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

require_once(dirname(__FILE__) . DS . "HtmlColumn.php");
require_once(dirname(__FILE__) . DS . ".." . DS . "CoverImage.php");

class HtmlCoverArtColumn extends HtmlColumn {

    public function __construct($colSpan = 1) {
        parent::__construct($colSpan);
    }

    protected function getHeaderText() {
        return JText::_("PLG_MP3BROWSER_HEADER_COVER_ART");
    }

    // tag reference: http://getid3.sourceforge.net/source2/structure.txt
    protected function getCellText($data, $isAlternate) {
        $cover = $data->getCover();
        $artist = $data->getArtist();
        $title = $data->getTitle();
        $alt = JText::_("PLG_MP3BROWSER_TOOLTIP_COVER_ART") . " " . $title;
        if ($artist != '') {
            $alt .= " (" . $artist . ")";
        }
        $html = "<img src=\"" . $cover->getSrc() . "\"";
        $html .= " alt=\"" . $alt . "\"";
        $html .= " title=\"" . $alt . "\"";
        $html .= " style=\"padding: 7px\"";
        $html .= ">";
        return $html;
    }

    public function isEmpty($data) {
        return !$data->hasCover() || !CoverImage::isBrowserSupported();
    }

}