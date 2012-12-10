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

class HtmlLiteralColumn extends HtmlColumn {

    private $headerText;
    private $cellText;

    public function __construct($headerText, $cellText, $colSpan = 1) {
        parent::__construct($colSpan);
        $this->headerText = $headerText;
        $this->cellText = $cellText;
    }

    protected function getHeaderText() {
        return $this->headerText;
    }

    protected function getCellText($data, $isAlternate) {
        return $this->cellText;
    }

}