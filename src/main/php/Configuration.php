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

class Configuration {
	private $registry;

	public function __construct(JRegistry $registry) {
		$this->registry = $registry;
	}

	private function get($path, $default=null) {
		return $this->registry->get($path, $default);
	}

	public function getMaxRows() {
		return $this->get('maxRows','20');
	}

	public function isShowDownload() {
		return $this->get('showDownload','1')==0 ? false : true;
	}

	public function isShowSize() {
		return $this->get('showSize','1')==0 ? false : true;
	}

	public function isShowLength() {
		return $this->get('showLength','1')==0 ? false : true;
	}

	public function isSortByAsc() {
		return $this->get('sortBy','0')==0 ? false : true;
	}

	public function getTableWidth() {
		$tableWidth = $this->get('tableWidth','');
		if( $tableWidth==0 ) {
			// legacy magic value
			$tableWidth = "100%";
		}
		else if ( preg_match("^[0-9]+$", $tableWidth)==1 ) {
			$tableWidth .= "px";
		}
		else  {
			$tableWidth = $tableWidth . 'px';
		}
		return $tableWidth;
	}

	public function getHeaderHeight() {
		return $this->get('headerHeight',35);
	}

	public function getRowHeight() {
		return $this->get('rowHeight',50);
	}

	public function getBottomRowBorderColor() {
		return $this->get('bottomRowBorder','#C0C0C0');
	}

	public function getPrimaryRowColor() {
		return $this->get('primaryRowColor','#ffffff');
	}

	public function getHeaderColor() {
		return $this->get('headerColor','#cccccc');
	}

	public function getAltRowColor() {
		return $this->get('altRowColor','#D6E3EB');
	}

	public function getDownloadColWidth() {
		return $this->get('downloadColWidth',90);
	}

	public function getDownloadImage() {
		$downloadImage = $this->get('downloadImage',0);
		if ( $downloadImage===0 ) {
			$downloadImage='downloadtune.jpg';
		}
		return $downloadImage;
	}

	public function getAltDownloadImage() {
		$downloadImage = $this->get('downloadImageAlt',0);
		if ( $downloadImage===0 ) {
			$downloadImage='downloadtune-blue.jpg';
		}
		return $downloadImage;
	}

	public function isBacklink() {
		return $this->get('backlink',1)==0 ? false : true;
	}

}
