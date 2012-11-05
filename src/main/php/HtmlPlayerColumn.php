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

class HtmlPlayerColumn extends HtmlColumn {
	private $configuration;
	
	public function __construct($configuration, $colSpan=0) {
		parent::__construct($colSpan);
		$this->configuration = $configuration;
		$this->addCssElement("width", "220px", true);
	}

	protected function getHeaderText() {
		return  JText::_("PLG_MP3BROWSER_HEADER_PLAY");
	}
	
	protected function getCellText($data, $isAlternate) {
		$html = "<object width=\"200\" height=\"20\" bgcolor=\"";
		
		if($isAlternate) {
			$html.=$this->configuration->getAltRowColor();
		}
		else {
			$html.=$this->configuration->getPrimaryRowColor();
		}

		$html .= "\" data=\"" . PluginHelper::getPluginBaseUrl() . "dewplayer.swf?son=" . $data->getUrlPath() . "&amp;autoplay=0&amp;autoreplay=0\" type=\"application/x-shockwave-flash\">  <param value=\"" . PluginHelper::getPluginBaseUrl() . "dewplayer.swf?son=" . $data->getUrlPath() . "&amp;autoplay=0&amp;autoreplay=0\" name=\"movie\"/><param value=\"";
		
		if($isAlternate) {
			$html.=$this->configuration->getAltRowColor();
		}
		else {
			$html.=$this->configuration->getPrimaryRowColor();
		}
		
		$html .= "\ name=\"bgcolor\"/></object><br/>";
		return $html;
	}
}