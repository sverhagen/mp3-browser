<?php
/**
 * @version		$Id: example.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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

	/**
	 * Example before display content method
	 *
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
		//		$app = JFactory::getApplication();

	 //Find all {music} tags in content items
		if ( preg_match_all("#{music}(.*?){/music}#s", $article->introtext, $matches, PREG_PATTERN_ORDER) > 0 || preg_match_all("#{music}(.*?){/music}#s", $article->text, $matches, PREG_PATTERN_ORDER) > 0 ) {
			$filepath = realpath (dirname(__FILE__));
			require_once($filepath.DS."Configuration.php");
			require_once($filepath.DS."Helper.php");
			require_once($filepath.DS."MusicItem.php");
			Helper::loadLanguage();

			//If found load config
			// j!1.5 paths
			$mosConfig_absolute_path = JPATH_SITE;
			$mosConfig_live_site = JURI :: base();
			if(substr($mosConfig_live_site, -1)=="/") $mosConfig_live_site = substr($mosConfig_live_site, 0, -1);
			$browserpath = $mosConfig_live_site . "/plugins/content/mp3browser/";

			require_once("getid3".DS."getid3".DS."getid3.php");

			$configuration = new Configuration($this->params);

			//Load Plugin parameters and set defaults
			$plugin =& JPluginHelper::getPlugin('content', 'mp3browser');

			foreach ($matches[0] as $match) {
				$musicPathTrail = preg_replace("/{.+?}/", "", $match);
					
				$html = $this->startHtml($configuration);
				//print table rows for each mp3 in directory
				//		if ( $handle = opendir( JPATH_SITE.DS . $musicPathTrail )) {

				$musicUrlPath = $mosConfig_live_site . "/" . $musicPathTrail;

				$dir_array = array();
				$i = 0;
				$count = 0;
				$narray = '';


				if(!JFolder::exists(JPATH_SITE.DS . $musicPathTrail)){
					$article->introtext = preg_replace( "#{music}".$musicPathTrail."{/music}#s", '' , $article->introtext );
					if(isset($article->text))
						$article->text = preg_replace( "#{music}".$musicPathTrail."{/music}#s", '' , $article->text );
					continue;
				}

				$narray = JFolder::files(JPATH_SITE.DS . $musicPathTrail);

				if( $configuration->isSortByAsc() )
					sort( $narray,SORT_STRING );
				else
					rsort( $narray,SORT_STRING );

				$numRows = sizeof( $narray );
				if ( $numRows > $configuration->getMaxRows() ) {
					$numRows = $configuration->getMaxRows();
				}

				for( $count=0; $count<$numRows; $count++ ) {
						
					$file = $narray[$count];
					$filePathName = JPATH_SITE.DS.$musicPathTrail.DS.$file;

					$getID3 = new getID3;

					$getID3->encoding = 'UTF-8';
					$ThisFileInfo = $getID3->analyze($filePathName);
					getid3_lib::CopyTagsToComments($ThisFileInfo);
					$musicItem = new MusicItem($filePathName, $ThisFileInfo);

					$html .= '
					<tr ';

					if ( $i ) $html .= 'class="colourblue"';

					$html .= ' style="text-align: left;">';

					//If Param is set to show download column.
					if( $configuration->isShowDownload() ) {

						// added 'downloadmp3.php' to force download
						$html .= '
						<td class="center">
						<span>
						<a href="' . $musicUrlPath . '/' . $file . '" title="Download Audio File" target="_blank" class="jce_file_custom">
						<img src="' . $browserpath;

						if( $i ) $html .= $configuration->getAltDownloadImage();
						else $html .= $configuration->getDownloadImage();

						$html .= '" alt="download" />
						</a>
						</span>
						</td>';
					}

					$html .= '
					<td ';

					if( !$configuration->isShowDownload() ) $html .= 'style="padding-left:10px;"';
						
					$html .= '><strong>'.$musicItem->getTitle().'</strong><br/>' . $musicItem->getArtist() . '</td>
					<td>
					<object width="200" height="20" bgcolor="';

					$i == '1'?$html.=$configuration->getAltRowColor():$html.=$configuration->getPrimaryRowColor();
					//$musicUrlPath = str_replace(array('https://','http://'), array('',''), $musicUrlPath);
					//$musicUrlPath = urlencode($musicUrlPath);
					//$musicUrlPath = JPATH_ROOT .DS. $musicPathTrail;
					$html .= '" data="' . $browserpath . 'dewplayer.swf?son=' . $musicUrlPath . '/' . $file . '&amp;autoplay=0&amp;autoreplay=0" type="application/x-shockwave-flash">  <param value="' . $browserpath . 'dewplayer.swf?son=' . $musicUrlPath . DS . $file . '&amp;autoplay=0&amp;autoreplay=0" name="movie"/><param value="';

					$i == '1'?$html.=$configuration->getAltRowColor():$html.=$configuration->getPrimaryRowColor();

					$html .= '" name="bgcolor"/></object><br/>
					</td>';

					if ( $configuration->isShowSize() ) {
						$html .= '
						<td>'.$musicItem->getFileSize().'</td>';
					}

					if ( $configuration->isShowLength() ) {
						$html .= '
						<td>'.$musicItem->getPlayTime().'<br/></td>
						</tr>';
					}

					$i = 1-$i;
				}
				//}

				if ( !$configuration->isBacklink() ){
					$display = "display:none;";
				}
				else
				{
					$display = "";
				}

				$html .= '
				<tr style="height:30px !important;">
				<td colspan="5" style="height:26px !important;">
				<div style="text-align:right; height:26px !important;'  . $display . '"><a href="http://www.dotcomdevelopment.com" style="color:'  . $configuration->getHeaderColor() . ' !important; font-size:10px; letter-spacing:0px; word-spacing:-1px; font-weight:normal;" title="Joomla web design Birmingham">Joomla! <h2 style="display:inline !important;font-size:10px !important; font-weight:normal !important;color:'  . $configuration->getHeaderColor() . ' !important;">web design birmingham</h2>...</a>&nbsp;</div>
				</td>
				</tr>
				';

				$html .= '
				</table>
				<!-- END: mp3 Browser -->

				';

				$article->introtext = preg_replace( "#{music}".$musicPathTrail."{/music}#s", $html , $article->introtext );
				if(isset($article->text))
					$article->text = preg_replace( "#{music}".$musicPathTrail."{/music}#s", $html , $article->text );
			}

		}
		return '';
	}
	private function startHtml($configuration)
	{
		//print table styles
		$html = '
			
			
		<!-- START: mp3 Browser -->
		<style type="text/css">
		table.mp3browser td.center { text-align:center; }
		table.mp3browser td { text-align:left; height:' . $configuration->getRowHeight() . 'px }
		.mp3browser tr.musictitles td { height:' . $configuration->getHeaderHeight() . 'px; }
		.mp3browser tr.musictitles { vertical-align:middle; background-color:'  . $configuration->getHeaderColor() . '; font-weight:bold; margin-bottom:15px; }
		.mp3browser td, .mp3browser th { padding:1px; vertical-align:middle; }
		.musictable { border-bottom:1px solid ' . $configuration->getBottomRowBorderColor() . '; text-align:left; height:' . $configuration->getRowHeight() . 'px; vertical-align:middle; }
		.mp3browser tr {background-color:' . $configuration->getPrimaryRowColor() . ' }
		.mp3browser a:link, .mp3browser a:visited { color:#1E87C8; text-decoration:none; }
		.mp3browser .colourblue { background-color:' . $configuration->getAltRowColor() . '; border-bottom:1px solid #C0C0C0; text-align:left; }
		</style>
		';

		//print table headers
		$html .= '
		<table width="' . $configuration->getTableWidth() . '" cellspacing="0" cellpadding="0" border="0" class="mp3browser" style="text-align: left;">
		<tr class="musictitles">';
			
		if( $configuration->isShowDownload() ) {
			$html .= '
			<td style="width:' . $configuration->getDownloadColWidth() .'px;text-align:center;">' . JText::_('PLG_MP3BROWSER_HEADER_DOWNLOAD') . '</td>';
		}
			
		$html .= '
		<td ';
			
		if( !$configuration->isShowDownload() ) $html .= 'style="padding-left:10px;"';
			
		$html .= '>' . JText::_('PLG_MP3BROWSER_HEADER_NAME') . '</td>
		<td width="220">' . JText::_('PLG_MP3BROWSER_HEADER_PLAY') . '</td>';
			
		if($configuration->isShowSize()){
			$html .= '
			<td width="60">' . JText::_('PLG_MP3BROWSER_HEADER_SIZE') . '</td>';
		}
			
		if( $configuration->isShowLength() ) {
			$html .= '
			<td width="70">' . JText::_('PLG_MP3BROWSER_HEADER_DURATION') . '</td>';
		}
		$html .= '
		</tr>';
		return $html;
	}
}