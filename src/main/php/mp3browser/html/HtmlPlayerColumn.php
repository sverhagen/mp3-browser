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

require_once(__DIR__ . DS . "HtmlColumn.php");

class HtmlPlayerColumn extends HtmlColumn {

    private $configuration;

    public function __construct($configuration, $colSpan = 1) {
        parent::__construct($colSpan);
        $this->configuration = $configuration;
        $width = $this->configuration->isVolumeControl() ? "260px" : "240px";
        $this->addCssElement("width", $width, true);
    }

    protected function getHeaderText() {
        return JText::_("PLG_MP3BROWSER_HEADER_PLAY");
    }

    protected function getCellText($data, $isAlternate) {
        $volumeControl = $this->configuration->isVolumeControl();
        $width = $volumeControl ? "240" : "220";
        $playerFile = $volumeControl ? "dewplayer-vol.swf" : "dewplayer.swf";
        $playerPath = PluginHelper::getPluginBaseUrl() . "dewplayer/" . $playerFile;
        $html = "<object width=\"" . $width . "\" height=\"20\" bgcolor=\"";

        if ($isAlternate) {
            $html .= $this->configuration->getAltRowColor();
        } else {
            $html .= $this->configuration->getPrimaryRowColor();
        }

        $html .= "\" data=\"" . $playerPath . "\" type=\"application/x-shockwave-flash\">";
        $html .= "<param name=\"wmode\" value=\"transparent\" />";
        $html .= "<param name=\"movie\" value=\"" . $playerPath . "\" />";
        $html .= "<param name=\"flashvars\" value=\"mp3=" . $data->getUrlPath() . "\" />";

        $html .= "</object><br/>";
        return $html;
    }

}