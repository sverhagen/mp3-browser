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

	public function __construct($colSpan=1) {
		$this->colSpan = $colSpan;
	}

	abstract protected function getHeaderText();

	abstract protected function getCellText($data, $isAlternate);

	public function getHeader() {
		$style = $this->getHeaderStyle();
		$span = $this->getColSpanString();
		return "<th".$span.$style.">".$this->getHeaderText()."</th>";
	}

	public function getCell($data, $isAlternate) {
		$html = "<td";
		$html .= $this->getClass();
		$html .= $this->getStyle();
		$html .= $this->getColSpanString();
		$html .= ">";
		if($this->isEmpty($data)) {
			$html .= "&nbsp;";
		}
		else {
			$html .= $this->getCellText($data, $isAlternate);
		}
		$html .= "</td>";
		return $html;
	}

	private function getColSpanString() {
		if($this->colSpan<=1) {
			return "";
		}
		else {
			return " colspan=\"" . $this->colSpan . "\"";
		}
	}

	public function getColSpan() {
		return $this->colSpan;
	}

	public function setColSpan($colSpan) {
		$this->colSpan = $colSpan;
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
	
	public function isEmpty($data) {
		return false;
	}
}