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

require_once(__DIR__.DS."HtmlColumn.php");

class HtmlDownloadColumn extends HtmlColumn {
	private $configuration;

	public function __construct($configuration, $colSpan=0) {
		parent::__construct($colSpan);
		$this->configuration = $configuration;
		$this->addCssElement("text-align", "center");
		$this->addCssElement("text-align", "center", true);
		$this->addCssElement("width", $this->configuration->getDownloadColWidth() . "px", true);
	}

	protected function getHeaderText() {
		return JText::_("PLG_MP3BROWSER_HEADER_DOWNLOAD");
	}

	protected function getCellText($data, $isAlternate) {
		$html = "<span>";
		$html .= "<a href=\"" . $data->getUrlPath() . "\" title=\"Download Audio File\" target=\"_blank\" class=\"jce_file_custom\">";
		$html .= "<img src=\"" . PluginHelper::getPluginBaseUrl();

		if( $isAlternate ) {
			$html .= $this->configuration->getAltDownloadImage();
		}
		else {
			$html .= $this->configuration->getDownloadImage();
		}

		$html .= "\" alt=\"download\" />";
		$html .= "</a>";
		$html .= "</span>";
		return $html;

	}

	protected function getClassName() {
		return "center";
	}
}