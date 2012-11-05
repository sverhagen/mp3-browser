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

class HtmlTable {
	private $configuration;

	private $columns = array();

	private $html = "";

	private $rowCount = 0;

	public function __construct($configuration) {
		$this->configuration = $configuration;
	}

	public function addColumn($column) {
		$this->columns[] = $column;
	}

	public function addData($data) {
		$this->rowCount++;
		$isAlternate = $this->rowCount % 2 == 0;
		$this->html .= "<tr style=\"text-align:left;\"";
		if ( $isAlternate ) {
			$this->html .= " class=\"colourblue\"";
		}
		$this->html .= ">";
		foreach($this->columns as $column) {
			$this->html .= $column->getCell($data, $isAlternate);
		}
		$this->html .= "</tr>";
	}

	public function finish() {
		if ( $this->configuration->isBacklink() ) {
			$message = "<div style=\"text-align:right; height:26px !important;\">";
			$message .= "<a href=\"http://www.dotcomdevelopment.com\"";
			$message .= " style=\"";
			$message .= "color:"  . $this->configuration->getHeaderColor() . " !important;";
			$message .= " font-size:10px;";
			$message .= " letter-spacing:0px;";
			$message .= " word-spacing:-1px;";
			$message .= " font-weight:normal;\"";
			$message .= " title=\"Joomla web design Birmingham\">Joomla! web design birmingham</a>";
			$message .= "</div>";
			$this->messageRow($message);
		}
		$this->html .= "</tbody>";
		$this->html .= "</table>";
		$this->html .= "<!-- END: mp3 Browser -->";
	}

	public function reset() {
		$this->html = "";
	}

	public function start() {
		$this->reset();
		$this->html .= "<!-- START: mp3 Browser -->";
		$this->includeStyling();
		$this->html .= "<table width=\"" . $this->configuration->getTableWidth() . "\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"mp3browser\" style=\"text-align: left;\">";
		$this->html .= "<thead>";
		$this->html .= "<tr class=\"musictitles\">";
		foreach($this->columns as $column) {
			$this->html .= $column->getHeader();
		}
		$this->html .= "</tr>";
		$this->html .= "</thead>";
		$this->html .= "<tbody>";
	}

	public function getHtml() {
		return $this->html;
	}

	public function messageRow($message) {
		$this->html .= "<tr>";
		$this->html .= "<td colspan=\"" . count($this->columns) . "\">";
		$this->html .= $message;
		$this->html .= "</td>";
		$this->html .= "</tr>";
	}

	private function includeStyling() {
		$this->html .= "<style type=\"text/css\">";
		$this->html .= "table.mp3browser td.center { text-align:center; }";
		$this->html .= "table.mp3browser td { text-align:left; height:" . $this->configuration->getRowHeight() . "px }";
		$this->html .= ".mp3browser thead tr.musictitles th { height:" . $this->configuration->getHeaderHeight() . "px; }";
		$this->html .= ".mp3browser thead tr.musictitles { vertical-align:middle; background-color:"  . $this->configuration->getHeaderColor() . "; font-weight:bold; margin-bottom:15px; }";
		$this->html .= ".mp3browser td, .mp3browser th { padding:1px; vertical-align:middle; }";
		$this->html .= ".musictable { border-bottom:1px solid " . $this->configuration->getBottomRowBorderColor() . "; text-align:left; height:" . $this->configuration->getRowHeight() . "px; vertical-align:middle; }";
		$this->html .= ".mp3browser tr {background-color:" . $this->configuration->getPrimaryRowColor() . " }";
		$this->html .= ".mp3browser a:link, .mp3browser a:visited { color:#1E87C8; text-decoration:none; }";
		$this->html .= ".mp3browser .colourblue { background-color:" . $this->configuration->getAltRowColor() . "; border-bottom:1px solid #C0C0C0; text-align:left; }";
		$this->html .= "</style>";
	}
}