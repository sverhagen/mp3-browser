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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "AbstractHtmlTable.php");

class HtmlDivTable extends AbstractHtmlTable {

    protected function addRowTypeData($rowType, $data) {
        $isAlternate = $this->isAlternate();
        if ($isAlternate) {
            $this->addHtmlLine("<div class=\"mp3browser-row mp3browser-alternate\">");
        } else {
            $this->addHtmlLine("<div class=\"mp3browser-row\">");
        }
        foreach ($rowType as $column) {
            $this->addHtmlLine($column->getDiv($data, $isAlternate));
        }
        $this->addHtmlLine("<div class=\"mp3browser-separator\"></div>");
        $this->addHtmlLine("</div>");
    }

    public function finish() {
        if ($this->getConfiguration()->isBacklink()) {
            $this->addBackLink();
        }
        $this->addHtmlLine("</div>");
        $this->addHtmlLine("<!-- END: mp3 Browser -->");
    }

    public function start($rowTypeNames) {
        $this->reset();
        $this->addHtmlLine("<!-- START: mp3 Browser -->");
        $this->includeStyling();
        $this->addHtmlLine("<div class=\"mp3browser\">");

        foreach ($rowTypeNames as $rowTypeName) {
            $this->addHtmlLine("<div class=\"mp3browser-headerRow\">");
            $rowType = $this->getRowType($rowTypeName);
            foreach ($rowType as $column) {
                $this->addHtmlLine($column->getHeaderDiv());
            }
            $this->addHtmlLine("<div class=\"mp3browser-separator\"></div>");
            $this->addHtmlLine("</div>");
        }
    }

    private function includeStyling() {
        $this->addHtmlLine("<style type=\"text/css\">");
        $this->addHtmlLine(".mp3browser");
        $this->addHtmlLine("{width:" . $this->getConfiguration()->getTableWidth() . "}");
        $this->addHtmlLine(".mp3browser");
        $this->addHtmlLine("{border-bottom:1px solid " . $this->getConfiguration()->getBottomRowBorderColor() . ";text-align:left}");
        $this->addHtmlLine(".mp3browser .center");
        $this->addHtmlLine("{text-align:center;}");
        $this->addHtmlLine(".mp3browser div.mp3browser-row div:not(.mp3browser-separator), .mp3browser div.mp3browser-headerRow div:not(.mp3browser-separator)");
        $this->addHtmlLine("{float:left;padding:5px}");
        $this->addHtmlLine(".mp3browser div.mp3browser-headerRow, .mp3browser div.mp3browser-headerRow div");
        $this->addHtmlLine("{vertical-align:middle; background-color:" . $this->getConfiguration()->getHeaderColor() . "; font-weight:bold}");
        $this->addHtmlLine(".mp3browser a:link, .mp3browser a:visited");
        $this->addHtmlLine("{color:#1E87C8;text-decoration:none;font-weight:inherit}");
        $this->addHtmlLine(".mp3browser div.mp3browser-row");
        $this->addHtmlLine("{clear:both;border-bottom:1px solid " . $this->getConfiguration()->getBottomRowBorderColor() . "}");
        $this->addHtmlLine(".mp3browser div.mp3browser-row, .mp3browser div.mp3browser-row div");
        $this->addHtmlLine("{background-color:" . $this->getConfiguration()->getPrimaryRowColor() . "}");
        $this->addHtmlLine(".mp3browser div.mp3browser-alternate, .mp3browser div.mp3browser-alternate div");
        $this->addHtmlLine("{background-color:" . $this->getConfiguration()->getAltRowColor() . "}");
        $this->addHtmlLine(".mp3browser-separator");
        $this->addHtmlLine("{float:none;clear:both;}");
        $this->addHtmlLine("</style>");
    }

}
