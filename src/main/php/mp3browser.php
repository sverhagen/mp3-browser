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

// No direct access
defined("_JEXEC") or die;

jimport("joomla.plugin.plugin");

require_once(__DIR__.DS."Configuration.php");
require_once(__DIR__.DS."HtmlDownloadColumn.php");
require_once(__DIR__.DS."HtmlDummyColumn.php");
require_once(__DIR__.DS."HtmlNameColumn.php");
require_once(__DIR__.DS."HtmlPlayerColumn.php");
require_once(__DIR__.DS."HtmlSimpleColumn.php");
require_once(__DIR__.DS."HtmlTable.php");
require_once(__DIR__.DS."MusicFolder.php");
require_once(__DIR__.DS."MusicTagsHelper.php");
require_once(__DIR__.DS."PluginHelper.php");

/**
 * Example Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 * @since		1.5
 */
class plgContentMp3browser extends JPlugin
{
	const DEFAULT_ROW_TYPE = "default"; 

	private $configuration;
	
	private $htmlTable;

	/**
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  Note $article->text is also available
	 * @param	object		The content params
	 * @param	int			The "page" number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart="")
	{
		$matches = MusicTagsHelper::getMusicTagsFromArticle($article);
		if ( count($matches) ) {
			$this->initializePlugin();
			$this->initializeHtmlTable();

			foreach ($matches as $musicPathTrail) {
				$this->handleSingleMusicPath($article, $musicPathTrail);
			}
		}
		return "";
	}

	private function handleSingleMusicPath($article, $musicPathTrail)
	{
		$this->htmlTable->start(array(self::DEFAULT_ROW_TYPE));

		$musicFolder = new MusicFolder($musicPathTrail);
		if($musicFolder->isExists()) {
			$sortByAsc = $this->configuration->isSortByAsc();
			$maxRows = $this->configuration->getMaxRows();
			$musicItems = $musicFolder->getMusicItems($sortByAsc, $maxRows);

			for( $count=0; $count<count($musicItems); $count++ ) {
				$musicItem = $musicItems[$count];
				$this->htmlTable->addData(array(self::DEFAULT_ROW_TYPE), $musicItem);
			}
		}
		else
		{
			$this->htmlTable->messageRow(JText::_("PLG_MP3BROWSER_NOITEMS"));
		}

		$this->htmlTable->finish();

		MusicTagsHelper::replaceTagsWithContent($article, $musicPathTrail, $this->htmlTable);
	}

	private function initializePlugin()
	{
		PluginHelper::loadLanguage();

		$this->configuration = new Configuration($this->params);
	}
	
	private function initializeHtmlTable()
	{
		$this->htmlTable = new HtmlTable($this->configuration);
		if( $this->configuration->isShowDownload() ) {
			$this->htmlTable->addColumn(self::DEFAULT_ROW_TYPE, new HtmlDownloadColumn($this->configuration));
		}
		$column = new HtmlNameColumn();
		$this->htmlTable->addColumn(self::DEFAULT_ROW_TYPE, $column);
		if( !$this->configuration->isShowDownload() ) {
			// dirty hack, immitating legacy code
			$column->addCssElement("padding-left", "10px", true);
			$column->addCssElement("padding-left", "10px");
		}
		$this->htmlTable->addColumn(self::DEFAULT_ROW_TYPE, new HtmlPlayerColumn($this->configuration));
		if($this->configuration->isShowSize()){
			$column = new HtmlSimpleColumn(JText::_("PLG_MP3BROWSER_HEADER_SIZE"), "getFileSize");
			$column->addCssElement("width", "60px", true);
			$this->htmlTable->addColumn(self::DEFAULT_ROW_TYPE, $column);
		}
		if ( $this->configuration->isShowLength() ) {
			$column = new HtmlSimpleColumn(JText::_("PLG_MP3BROWSER_HEADER_DURATION"), "getPlayTime");
			$column->addCssElement("width", "70px", true);
			$this->htmlTable->addColumn(self::DEFAULT_ROW_TYPE, $column);
		}
	}
}