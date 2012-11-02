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
		$app = JFactory::getApplication();	

		$lang = JFactory::getLanguage();
		$lang->load('plg_content_mp3browser', JPATH_ADMINISTRATOR);
		
	 //Find all {music} tags in content items
	if ( preg_match_all("#{music}(.*?){/music}#s", $article->introtext, $matches, PREG_PATTERN_ORDER) > 0 || preg_match_all("#{music}(.*?){/music}#s", $article->text, $matches, PREG_PATTERN_ORDER) > 0 ) {
	
	//If found load config
		// j!1.5 paths
	$mosConfig_absolute_path = JPATH_SITE;
	$mosConfig_live_site = JURI :: base();
	if(substr($mosConfig_live_site, -1)=="/") $mosConfig_live_site = substr($mosConfig_live_site, 0, -1);
	$browserpath = $mosConfig_live_site . "/plugins/content/mp3browser/";
	
	require_once("getid3".DS."getid3".DS."getid3.php");

	//Load Plugin parameters and set defaults 
	$plugin =& JPluginHelper::getPlugin('content', 'mp3browser');

	$maxRows = $this->params->get('maxRows', '20');
	$showDownload = $this->params->get('showDownload', '1');
	$showSize = $this->params->get('showSize', '1');
	$showLength = $this->params->get('showLength', '1');	
	$sortByAsc = $this->params->get('sortBy', '0');
	
	$tableWidth = $this->params->get('tableWidth', 0);
	
	if ( $tableWidth==0 ) $tableWidth = '100%';
	else $tableWidth = $tableWidth . 'px';
	
	$headerHeight = $this->params->get('headerHeight', 35);
	$rowHeight = $this->params->get('rowHeight', 50);
	$bottomRowBorder = $this->params->get('bottomRowBorder', '#C0C0C0');
	$primaryRowColor = $this->params->get('primaryRowColor', '#ffffff');
	$headerColor = $this->params->get('headerColor', '#cccccc');
	$altRowColor = $this->params->get('altRowColor', '#D6E3EB');
	$downloadColWidth = $this->params->get('downloadColWidth', 90);
	$downloadImage = $this->params->get('downloadImage', 0);
	
	$backlink = $this->params->get('backlink', 1);
	
	if ( $downloadImage===0 ) {
	    $downloadImage='downloadtune.jpg';
	}
	
	$downloadImageAlt = $this->params->get('downloadImageAlt', 0);
	
	if ( $downloadImageAlt===0 ) {
	    $downloadImageAlt='downloadtune-blue.jpg';
	}
	
	foreach ($matches[0] as $match) {
		$_temp = preg_replace("/{.+?}/", "", $match);
			
		//print table styles
		$html = '
		
		
    		<!-- START: mp3 Browser --> 
    		<style type="text/css">
    			table.mp3browser td.center { text-align:center; }
    			table.mp3browser td { text-align:left; height:' . $rowHeight . 'px } 
    			.mp3browser tr.musictitles td { height:' . $headerHeight . 'px; } 
    			.mp3browser tr.musictitles { vertical-align:middle; background-color:'  . $headerColor . '; font-weight:bold; margin-bottom:15px; }
                .mp3browser td, .mp3browser th { padding:1px; vertical-align:middle; }
                .musictable { border-bottom:1px solid ' . $bottomRowBorder . '; text-align:left; height:' . $rowHeight . 'px; vertical-align:middle; }
                .mp3browser tr {background-color:' . $primaryRowColor . ' }
                .mp3browser a:link, .mp3browser a:visited { color:#1E87C8; text-decoration:none; }
                .mp3browser .colourblue { background-color:' . $altRowColor . '; border-bottom:1px solid #C0C0C0; text-align:left; }
            </style>
        ';
			
		//print table headers
		$html .= '
    		<table width="' . $tableWidth . '" cellspacing="0" cellpadding="0" border="0" class="mp3browser" style="text-align: left;">
    		    <tr class="musictitles">';
		
		if( $showDownload ) {
		    $html .= '
		            <td style="width:' . $downloadColWidth .'px;text-align:center;">' . JText::_('PLG_MP3BROWSER_HEADER_DOWNLOAD') . '</td>';
		}
		
		$html .= '
		            <td ';
		        
		if( !$showDownload ) $html .= 'style="padding-left:10px;"';
		
		$html .= '>' . JText::_('PLG_MP3BROWSER_HEADER_NAME') . '</td>
		            <td width="220">' . JText::_('PLG_MP3BROWSER_HEADER_PLAY') . '</td>';
		
		if($showSize){
		    $html .= '
		            <td width="60">' . JText::_('PLG_MP3BROWSER_HEADER_SIZE') . '</td>';
		}
		
		if( $showLength ) {
		    $html .= '
		            <td width="70">' . JText::_('PLG_MP3BROWSER_HEADER_DURATION') . '</td>';
		}
		$html .= '
		        </tr>';
		
		//print table rows for each mp3 in directory
//		if ( $handle = opendir( JPATH_SITE.DS . $_temp )) {
		    
			$musicDir = $mosConfig_live_site . "/" . $_temp;
            
            // added for force download 
            // NOT USED DUE TO THE HUGE SECURITY WHOLE THAT THIS EXPOSES BY ENABLING USERS TO DOWNLOAD ALL SITE FILES
            $mp3dldir = $_temp;
            
			$dir_array = array();
			$i = 0;
			$count = 0;
			$narray = '';
			
			
			if(!JFolder::exists(JPATH_SITE.DS . $_temp)){
				$article->introtext = preg_replace( "#{music}".$_temp."{/music}#s", '' , $article->introtext );
				if(isset($article->text))
					$article->text = preg_replace( "#{music}".$_temp."{/music}#s", '' , $article->text );				
				continue;	
			}
			
			$narray = JFolder::files(JPATH_SITE.DS . $_temp);
			
			if( $sortByAsc )
			    sort( $narray,SORT_STRING );
			else
			    rsort( $narray,SORT_STRING );
			
			$numRows = sizeof( $narray );
			if ( $numRows > $maxRows ) {
			  $numRows = $maxRows;
			}
			
        	for( $count=0; $count<$numRows; $count++ ) {
        	    
        		$file = $narray[$count];
        		$artist = '';
        		$filesize = '';
        		$filetoget = JPATH_SITE.DS.$_temp.DS.$file;
				
				$getID3 = new getID3;
				
				$getID3->encoding = 'UTF-8';
				$ThisFileInfo = $getID3->analyze($filetoget);
				getid3_lib::CopyTagsToComments($ThisFileInfo);
				
				//If title tag found use that else use file name -mp3
				if ( isset( $ThisFileInfo['comments']['title'][0] ) )
					$title = $ThisFileInfo['comments']['title'][0];
				else $title = substr($file,0,-4);
				
			    //Calculate filesize
			    $filesize = ( filesize(JPATH_SITE.DS.$_temp.DS.$file) * .0009765625 ) * .0009765625;
			    $filesize = round($filesize, 1);
			   
			    //print artist name if present
				if ( isset ( $ThisFileInfo['comments']['artist'][0] ) ){
					$artist = '' . $ThisFileInfo['comments']['artist'][0] . '';
				}
				
				$playtime = $ThisFileInfo [ 'playtime_string' ];
				$html .= '
				<tr ';
				
				if ( $i ) $html .= 'class="colourblue"';
				
				$html .= ' style="text-align: left;">';
				
				//If Param is set to show download column.
				if( $showDownload ) {
				    
				    // added 'downloadmp3.php' to force download
    				$html .= '
    				<td class="center">
    				    <span>
    				        <a href="' . $musicDir . '/' . $file . '" title="Download Audio File" target="_blank" class="jce_file_custom">
    				            <img src="' . $browserpath;
                    
                    if( $i ) $html .= $downloadImageAlt; 
                    else $html .= $downloadImage;
                    
    				$html .= '" alt="download" />
    				        </a>
    				    </span>
    				</td>';
				}
				
		        $html .= '
		            <td ';
		        
			    if( !$showDownload ) $html .= 'style="padding-left:10px;"';
			    
		        $html .= '><strong>'.$title.'</strong><br/>' . $artist . '</td>
		            <td>
		                <object width="200" height="20" bgcolor="';
		        
				$i == '1'?$html.=$altRowColor:$html.=$primaryRowColor;
				//$musicDir = str_replace(array('https://','http://'), array('',''), $musicDir);
				//$musicDir = urlencode($musicDir);
				//$musicDir = JPATH_ROOT .DS. $_temp;
				$html .= '" data="' . $browserpath . 'dewplayer.swf?son=' . $musicDir . '/' . $file . '&amp;autoplay=0&amp;autoreplay=0" type="application/x-shockwave-flash">  <param value="' . $browserpath . 'dewplayer.swf?son=' . $musicDir . DS . $file . '&amp;autoplay=0&amp;autoreplay=0" name="movie"/><param value="';
				
				$i == '1'?$html.=$altRowColor:$html.=$primaryRowColor;
				
				$html .= '" name="bgcolor"/></object><br/>
				    </td>';
				
				if ( $showSize ) {
				    $html .= '
				    <td>'.$filesize.' MB</td>';
				}
				
				if ( $showLength ) {
				    $html .= '
				    <td>'.$playtime.' min<br/></td>
				</tr>';
				}

				$i = 1-$i;
			}
		//}
		
		if ( !$backlink ) $display = "display:none;";
		    
	    $html .= '
		    <tr style="height:30px !important;">
	            <td colspan="5" style="height:26px !important;">
	                <div style="text-align:right; height:26px !important;'  . $display . '"><a href="http://www.dotcomdevelopment.com" style="color:'  . $headerColor . ' !important; font-size:10px; letter-spacing:0px; word-spacing:-1px; font-weight:normal;" title="Joomla web design Birmingham">Joomla! <h2 style="display:inline !important;font-size:10px !important; font-weight:normal !important;color:'  . $headerColor . ' !important;">web design birmingham</h2>...</a>&nbsp;</div>
	            </td>
	        </tr>
	    ';
		
		$html .= '
		    </table>
		    <!-- END: mp3 Browser -->
		    
		';

		$article->introtext = preg_replace( "#{music}".$_temp."{/music}#s", $html , $article->introtext ); 
		if(isset($article->text))
			$article->text = preg_replace( "#{music}".$_temp."{/music}#s", $html , $article->text );
		}
		
	}
	return '';
	}


	/**
	 * Example prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The content object.  Note $article->text is also available
	 * @param	object	The content params
	 * @param	int		The 'page' number
	 * @since	1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
	}
}