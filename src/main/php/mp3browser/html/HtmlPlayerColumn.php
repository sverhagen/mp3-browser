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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "HtmlColumn.php");

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
        $bgColor = $isAlternate ? $this->configuration->getAltRowColor() : $this->configuration->getPrimaryRowColor();
        $playerCode = $this->configuration->getPlayerCode();

        $playerCode = str_replace("%1", $width, $playerCode);
        $playerCode = str_replace("%2", $bgColor, $playerCode);
        $playerCode = str_replace("%3", $data->getUrlPath(), $playerCode);
        $playerCode = str_replace("%4", $playerPath, $playerCode);

        return $playerCode . "<br/>";
    }

}