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
abstract class AbstractHtmlTable {

    private $configuration;
    // key: row type name; value: columns
    private $rowTypes = array();
    private $html = "";
    private $rowCount = 0;

    public function __construct($configuration) {
        $this->configuration = $configuration;
    }

    public function addColumn($rowTypeName, $column) {
        $this->rowTypes[$rowTypeName][] = $column;
    }

    protected function isAlternate() {
        return $this->rowCount % 2 == 0;
    }

    public function addData($rowTypeNames, $data) {
        $this->rowCount++;
        foreach ($rowTypeNames as $rowTypeName) {
            if (!isset($this->rowTypes[$rowTypeName])) {
                continue;
            }
            $rowType = $this->rowTypes[$rowTypeName];

            $empty = true;
            foreach ($rowType as $column) {
                if (!$column->isEmpty($data)) {
                    $empty = false;
                    $break;
                }
            }

            if ($empty) {
                continue;
            }

            $this->addRowTypeData($rowType, $data);
        }
    }

    abstract protected function addRowTypeData($rowType, $data);

    protected function addBackLink() {
        $this->addHtmlLine("<div style=\"text-align:right; height:26px !important;\">");
        $this->addHtmlLine("<a href=\"http://www.dotcomdevelopment.com\"");
        $this->addHtmlLine(" style=\"");
        $this->addHtmlLine("color:" . $this->configuration->getHeaderColor() . " !important;");
        $this->addHtmlLine(" font-size:10px;");
        $this->addHtmlLine(" letter-spacing:0px;");
        $this->addHtmlLine(" word-spacing:-1px;");
        $this->addHtmlLine(" font-weight:normal;\"");
        $this->addHtmlLine(" title=\"Joomla web design Birmingham\">Joomla! web design Birmingham</a>");
        $this->addHtmlLine("</div>");
    }

    abstract public function finish();

    public function reset() {
        $this->rowCount = 0;
        $this->html = "";
    }

    abstract public function start($rowTypeNames);

    protected function addHtmlLine($htmlLine) {
        $this->addHtml($htmlLine . "\r\n");
    }

    protected function addHtml($html) {
        $this->html .= $html;
    }

    public function __toString() {
        return $this->getHtml();
    }

    public function getHtml() {
        return $this->html;
    }

    protected function getConfiguration() {
        return $this->configuration;
    }

    protected function getRowType($rowTypeName) {
        return $this->rowTypes[$rowTypeName];
    }

    protected function getRowTypes() {
        return $this->rowTypes;
    }

    public function getColumnCountForRowType($rowTypeName) {
        $columnCount = 0;
        foreach ($this->rowTypes[$rowTypeName] as $column) {
            $columnCount += $column->getColSpan();
        }
        return $columnCount;
    }

    public function getColumnCount() {
        $maxColumnCount = 0;
        foreach ($this->rowTypes as $rowTypeName => $rowType) {
            $columnCount = $this->getColumnCountForRowType($rowTypeName);
            $maxColumnCount = max($maxColumnCount, $columnCount);
        }
        return $maxColumnCount;
    }

}