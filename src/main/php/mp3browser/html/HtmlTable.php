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

class HtmlTable extends AbstractHtmlTable {

    protected function addRowTypeData($rowType, $data) {
        $isAlternate = $this->isAlternate();
        if ($isAlternate) {
            $this->addHtmlLine("<tr class=\"mp3browser-row mp3browser-alternate\">");
        } else {
            $this->addHtmlLine("<tr class=\"mp3browser-row\">");
        }
        foreach ($rowType as $column) {
            $this->addHtmlLine($column->getTableCell($data, $isAlternate));
        }
        $this->addHtmlLine("</tr>");
    }

    public function finish() {
        if ($this->getConfiguration()->isBacklink()) {
            $this->addHtmlLine("<tr>");
            $this->addHtmlLine("<td colspan=\"" . $this->getColumnCount() . "\">");
            $this->addBackLink();
            $this->addHtmlLine("</td>");
            $this->addHtmlLine("</tr>");
        }
        $this->addHtmlLine("</tbody>");
        $this->addHtmlLine("</table>");
        $this->addHtmlLine("<!-- END: mp3 Browser -->");
    }

    private function adjustColSpans() {
        $columnCount = $this->getColumnCount();
        foreach ($this->getRowTypes() as $key => $rowType) {
            $difference = $columnCount - count($rowType);
            $arrayValues = array_values($rowType);
            $lastColumn = end($arrayValues);
            $lastColSpan = max(1, $lastColumn->getColSpan());
            $lastColumn->setColSpan($lastColSpan + $difference);
        }
    }

    public function start($rowTypeNames) {
        $this->reset();
        $this->adjustColSpans();
        $this->addHtmlLine("<!-- START: mp3 Browser -->");
        $this->includeStyling();
        $this->addHtmlLine("<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"mp3browser\">");
        $this->addHtmlLine("<thead>");

        foreach ($rowTypeNames as $rowTypeName) {
            $this->addHtmlLine("<tr>");
            $rowType = $this->getRowType($rowTypeName);
            foreach ($rowType as $column) {
                $this->addHtmlLine($column->getHeaderCell());
            }
            $this->addHtmlLine("</tr>");
        }

        $this->addHtmlLine("</thead>");
        $this->addHtmlLine("<tbody>");
    }

    private function includeStyling() {
        $this->addHtmlLine("<style type=\"text/css\">");
        $this->addHtmlLine(".mp3browser");
        $this->addHtmlLine("{width:" . $this->getConfiguration()->getTableWidth() . "}");
        $this->addHtmlLine(".mp3browser");
        $this->addHtmlLine("{border-bottom:1px solid " . $this->getConfiguration()->getBottomRowBorderColor() . "; text-align:left; height:" . $this->getConfiguration()->getRowHeight() . "px; vertical-align:middle;}");
        $this->addHtmlLine(".mp3browser .center");
        $this->addHtmlLine("{text-align:center;}");
        $this->addHtmlLine(".mp3browser td");
        $this->addHtmlLine("{text-align:left; height:" . $this->getConfiguration()->getRowHeight() . "px}");
        $this->addHtmlLine(".mp3browser th");
        $this->addHtmlLine("{height:" . $this->getConfiguration()->getHeaderHeight() . "px;}");
        $this->addHtmlLine(".mp3browser thead tr");
        $this->addHtmlLine("{vertical-align:middle; background-color:" . $this->getConfiguration()->getHeaderColor() . "; font-weight:bold; margin-bottom:15px;}");
        $this->addHtmlLine(".mp3browser td, .mp3browser th");
        $this->addHtmlLine("{padding:1px; vertical-align:middle;}");
        $this->addHtmlLine(".mp3browser a:link, .mp3browser a:visited");
        $this->addHtmlLine("{color:#1E87C8; text-decoration:none; font-weight:inherit}");
        $this->addHtmlLine(".mp3browser tr");
        $this->addHtmlLine("{background-color:" . $this->getConfiguration()->getPrimaryRowColor() . "}");
        $this->addHtmlLine(".mp3browser .mp3browser-row");
        $this->addHtmlLine("{border-bottom:1px solid " . $this->getConfiguration()->getBottomRowBorderColor() . "; text-align:left;}");
        $this->addHtmlLine(".mp3browser .mp3browser-alternate");
        $this->addHtmlLine("{background-color:" . $this->getConfiguration()->getAltRowColor() . "}");
        $this->addHtmlLine("</style>");
    }

}
