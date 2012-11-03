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
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Example Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 * @since		1.5
 */
class plgContentMp3browser extends JPlugin
{
	private $configuration;

	/**
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  Note $article->text is also available
	 * @param	object		The content params
	 * @param	int			The 'page' number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart="")
	{
		require_once(__DIR__.DS."MusicTagsHelper.php");
		$matches = MusicTagsHelper::getMusicTagsFromArticle($article);
		if ( count($matches) ) {
			$this->initializePlugin();
			foreach ($matches as $musicPathTrail) {
				$this->handleSingleMusicPath($article, $musicPathTrail);
			}
		}
		return '';
	}
	
	private function handleSingleMusicPath($article, $musicPathTrail)
	{
		$html = $this->startHtml();
		
		require_once(__DIR__.DS."MusicFolder.php");
		$musicFolder = new MusicFolder($musicPathTrail);
		if($musicFolder->isExists()) {
			$sortByAsc = $this->configuration->isSortByAsc();
			$maxRows = $this->configuration->getMaxRows();
			$musicItems = $musicFolder->getMusicItems($sortByAsc, $maxRows);
		
			for( $count=0; $count<count($musicItems); $count++ ) {
				$musicItem = $musicItems[$count];
				$html .= $this->itemHtml($musicItem, $count % 2);
			}
		}
		else
		{
			$html .= "<tr><td colspan=\"5\">No items to display";
		}
		
		$html .= $this->finishHtml();
		
		MusicTagsHelper::replaceTagsWithContent($article, $musicPathTrail, $html);
	}

	private function initializePlugin()
	{
		require_once(__DIR__.DS."PluginHelper.php");
		PluginHelper::loadLanguage();

		require_once(__DIR__.DS."Configuration.php");
		$this->configuration = new Configuration($this->params);
	}

	private function finishHtml()
	{
		if ( !$this->configuration->isBacklink() ){
			$display = "display:none;";
		}
		else
		{
			$display = "";
		}

		$html = '
		<tr style="height:30px !important;">
		<td colspan="5" style="height:26px !important;">
		<div style="text-align:right; height:26px !important;'  . $display . '"><a href="http://www.dotcomdevelopment.com" style="color:'  . $this->configuration->getHeaderColor() . ' !important; font-size:10px; letter-spacing:0px; word-spacing:-1px; font-weight:normal;" title="Joomla web design Birmingham">Joomla! <h2 style="display:inline !important;font-size:10px !important; font-weight:normal !important;color:'  . $this->configuration->getHeaderColor() . ' !important;">web design birmingham</h2>...</a>&nbsp;</div>
		</td>
		</tr>
		';

		$html .= '
		</table>
		<!-- END: mp3 Browser -->

		';
		return $html;
	}
	private function itemHtml($musicItem, $alternateRow)
	{
		//If found load config
		// j!1.5 paths
		$mosConfig_absolute_path = JPATH_SITE;
		$mosConfig_live_site = JURI :: base();
		if(substr($mosConfig_live_site, -1)=="/") $mosConfig_live_site = substr($mosConfig_live_site, 0, -1);
		$browserpath = $mosConfig_live_site . "/plugins/content/mp3browser/";

		$html = '
		<tr ';

		if ( $alternateRow ) $html .= 'class="colourblue"';

		$html .= ' style="text-align: left;">';

		//If Param is set to show download column.
		if( $this->configuration->isShowDownload() ) {

			// added 'downloadmp3.php' to force download
			$html .= '
			<td class="center">
			<span>
			<a href="' . $musicItem->getUrlPath() . '" title="Download Audio File" target="_blank" class="jce_file_custom">
			<img src="' . $browserpath;

			if( $alternateRow ) $html .= $this->configuration->getAltDownloadImage();
			else $html .= $this->configuration->getDownloadImage();

			$html .= '" alt="download" />
			</a>
			</span>
			</td>';
		}

		$html .= '
		<td ';

		if( !$this->configuration->isShowDownload() ) $html .= 'style="padding-left:10px;"';

		$html .= '><strong>'.$musicItem->getTitle().'</strong><br/>' . $musicItem->getArtist() . '</td>
		<td>
		<object width="200" height="20" bgcolor="';

		$alternateRow == '1'?$html.=$this->configuration->getAltRowColor():$html.=$this->configuration->getPrimaryRowColor();
		//$musicUrlPath = str_replace(array('https://','http://'), array('',''), $musicUrlPath);
		//$musicUrlPath = urlencode($musicUrlPath);
		//$musicUrlPath = JPATH_ROOT .DS. $musicPathTrail;
		$html .= '" data="' . $browserpath . 'dewplayer.swf?son=' . $musicItem->getUrlPath() . '&amp;autoplay=0&amp;autoreplay=0" type="application/x-shockwave-flash">  <param value="' . $browserpath . 'dewplayer.swf?son=' . $musicItem->getUrlPath() . '&amp;autoplay=0&amp;autoreplay=0" name="movie"/><param value="';

		$alternateRow == '1'?$html.=$this->configuration->getAltRowColor():$html.=$this->configuration->getPrimaryRowColor();

		$html .= '" name="bgcolor"/></object><br/>
		</td>';

		if ( $this->configuration->isShowSize() ) {
			$html .= '
			<td>'.$musicItem->getFileSize().'</td>';
		}

		if ( $this->configuration->isShowLength() ) {
			$html .= '
			<td>'.$musicItem->getPlayTime().'</td>';
		}
		$html .= '</tr>';
		return $html;
	}
	private function startHtml()
	{
		//print table styles
		$html = '
			
			
		<!-- START: mp3 Browser -->
		<style type="text/css">
		table.mp3browser td.center { text-align:center; }
		table.mp3browser td { text-align:left; height:' . $this->configuration->getRowHeight() . 'px }
		.mp3browser tr.musictitles td { height:' . $this->configuration->getHeaderHeight() . 'px; }
		.mp3browser tr.musictitles { vertical-align:middle; background-color:'  . $this->configuration->getHeaderColor() . '; font-weight:bold; margin-bottom:15px; }
		.mp3browser td, .mp3browser th { padding:1px; vertical-align:middle; }
		.musictable { border-bottom:1px solid ' . $this->configuration->getBottomRowBorderColor() . '; text-align:left; height:' . $this->configuration->getRowHeight() . 'px; vertical-align:middle; }
		.mp3browser tr {background-color:' . $this->configuration->getPrimaryRowColor() . ' }
		.mp3browser a:link, .mp3browser a:visited { color:#1E87C8; text-decoration:none; }
		.mp3browser .colourblue { background-color:' . $this->configuration->getAltRowColor() . '; border-bottom:1px solid #C0C0C0; text-align:left; }
		</style>
		';

		//print table headers
		$html .= '
		<table width="' . $this->configuration->getTableWidth() . '" cellspacing="0" cellpadding="0" border="0" class="mp3browser" style="text-align: left;">
		<tr class="musictitles">';
			
		if( $this->configuration->isShowDownload() ) {
			$html .= '
			<td style="width:' . $this->configuration->getDownloadColWidth() .'px;text-align:center;">' . JText::_('PLG_MP3BROWSER_HEADER_DOWNLOAD') . '</td>';
		}
			
		$html .= '
		<td ';
			
		if( !$this->configuration->isShowDownload() ) $html .= 'style="padding-left:10px;"';
			
		$html .= '>' . JText::_('PLG_MP3BROWSER_HEADER_NAME') . '</td>
		<td width="220">' . JText::_('PLG_MP3BROWSER_HEADER_PLAY') . '</td>';
			
		if($this->configuration->isShowSize()){
			$html .= '
			<td width="60">' . JText::_('PLG_MP3BROWSER_HEADER_SIZE') . '</td>';
		}
			
		if( $this->configuration->isShowLength() ) {
			$html .= '
			<td width="70">' . JText::_('PLG_MP3BROWSER_HEADER_DURATION') . '</td>';
		}
		$html .= '
		</tr>';
		return $html;
	}
}