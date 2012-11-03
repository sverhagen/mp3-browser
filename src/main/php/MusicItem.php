<?php
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
