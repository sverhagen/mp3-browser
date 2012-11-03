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

class MusicItem {
	private $filePathName;

	private $getId3FileInfo;

	public function __construct($filePathName, $getId3FileInfo) {
		$this->filePathName = $filePathName;
		$this->getId3FileInfo = $getId3FileInfo;
	}

	public function getTitle() {
		if ( isset( $this->getId3FileInfo['comments']['title'][0] ) ) {
			return $this->getId3FileInfo['comments']['title'][0];
		}
		else {
			// use file name as an alternative
			$baseName = basename($this->filePathName);
			$lastDot = strrpos($baseName, ".");
			return substr($baseName, 0, $lastDot);
		}

	}

	public function getArtist() {
		if ( isset ( $this->getId3FileInfo['comments']['artist'][0] ) ) {
			return $this->getId3FileInfo['comments']['artist'][0];
		}
		else {
			return "";
		}
	}

	public function getPlayTime() {
		if ( isset ( $this->getId3FileInfo ['playtime_string'] ) ) {
			return $this->getId3FileInfo ['playtime_string']." min";
		}
		else {
			return "";
		}
	}

	public function getFileSize() {
		$fileSize = ( filesize($this->filePathName) * .0009765625 ) * .0009765625;
		$fileSize = round($fileSize, 1);
		return $fileSize." MB";
	}
}
