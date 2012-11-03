<?php
class Helper {
	public static function loadLanguage() {
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_mp3browser', JPATH_ADMINISTRATOR);
	}
}