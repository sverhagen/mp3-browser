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

abstract class HtmlColumn {
	private $colSpan;
	
	private $cssElements = array();
	
	private $headerCssElements = array();

	public function __construct($colSpan=0) {
		$this->colSpan = $colSpan;
	}

	abstract protected function getHeaderText();

	abstract protected function getCellText($data, $isAlternate);

	public function getHeader() {
		$style = $this->getHeaderStyle();
		$span = $this->getColSpan();
		return "<th".$span.$style.">".$this->getHeaderText()."</th>";
	}

	public function getCell($data, $isAlternate) {
		$class = $this->getClass();
		$style = $this->getStyle();
		$span = $this->getColSpan();
		return "<td".$class.$span.$style.">".$this->getCellText($data, $isAlternate)."</td>";
	}

	private function getColSpan() {
		if($this->colSpan==0) {
			return "";
		}
		else {
			return " colspan=\"" . $this->colSpan . "\"";
		}
	}

	private function getHeaderStyle() {
		if(count($this->headerCssElements)) {
			return " style=\"" . implode(";", $this->headerCssElements) . "\"";
		}
		else {
			return "";
		}
	}
	
	private function getStyle() {
		if(count($this->cssElements)) {
			return " style=\"" . implode(";", $this->cssElements) . "\"";
		}
		else {
			return "";
		}
	}
	
	private function getClass() {
		$className = $this->getClassName();
		if($className=="") {
			return "";
		}
		else {
			return " class=\"" . $className . "\"";
		}
	}
	
	public function addCssElement($name, $value, $header=false) {
		$cssElement = $name . ":" . $value;
		if($header) {
			$this->headerCssElements[] = $cssElement;
		}
		else {
			$this->cssElements[] = $cssElement;
		}
	}

	protected function getClassName() {
		return "";
	}
}