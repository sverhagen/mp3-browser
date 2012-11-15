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

require_once(__DIR__ . DS . "Configuration.php");
require_once(__DIR__ . DS . "CoverImage.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlCoverArtColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlCommentsColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlDownloadColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlDummyColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlLiteralColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlNameColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlPlayerColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlSimpleColumn.php");
require_once(__DIR__ . DS . "html" . DS . "HtmlTable.php");
require_once(__DIR__ . DS . "MusicFolder.php");
require_once(__DIR__ . DS . "MusicTagsHelper.php");
require_once(__DIR__ . DS . "PluginHelper.php");

/**
 * Example Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 * @since		1.5
 */
class plgContentMp3browser extends JPlugin {

    const DEFAULT_ROW = "default";
    const EXTENDED_INFO_ROW = "extended info";
    const NO_ITEMS_ROW = "no items";

    private $configuration;

    /**
     * Method is called by the view and the results are imploded and displayed in a placeholder.
     *
     * @param	string     The context for the content passed to the plugin.
     * @param	article    The article that is being rendered by the view.
     * @param	params     An associative array of relevant parameters.
     * @param	limitstart An integer that determines the "page" of the content that is to be generated. Due to the "all pages" option of a multi-page article giving $limitstart==0 we cannot use this to properly determine the offset for music folders.
     * @return	string     Returned value from this event will be displayed in a placeholder. Most templates display this placeholder after the article separator.
     * @since	1.6
     */
    public function onContentBeforeDisplay($context, &$article, &$params, $limitstart = "") {
        $this->initializePlugin();
        $musicTags = MusicTagsHelper::getMusicTagsFromArticle($article);
        if (count($musicTags)) {
            foreach ($musicTags as $musicTag) {
                $this->handleSingleMusicTag($article, $musicTag);
            }
        }
        return "";
    }

    private function handleSingleMusicTag($article, MusicTag $musicTag) {
        $musicTag->addConfiguration($this->configuration);
        $htmlTable = $this->getHtmlTableForMusicTag($musicTag);
        $htmlTable->start(array(self::DEFAULT_ROW));
        $musicFolder = new MusicFolder($musicTag);
        if (!$this->handleSingleMusicFolder($musicTag, $musicFolder, $htmlTable)) {
            // print empty table message
            $htmlTable->addData(array(self::NO_ITEMS_ROW), NULL);
        }
        $htmlTable->finish();
        $musicTag->setReplacementContent($htmlTable);
        MusicTagsHelper::replaceTagsWithReplacementContent($article, $musicTag);
    }

    private function initializePlugin() {
        PluginHelper::loadLanguage();

        $this->configuration = new Configuration($this->params);
    }

    private function initializeExtendedInfoColumns(MusicTag $musicTag, HtmlTable $htmlTable) {
        if ($musicTag->getConfiguration()->isShowExtendedInfo()) {
            if ($musicTag->getConfiguration()->isShowDownload()) {
                $htmlTable->addColumn(self::EXTENDED_INFO_ROW, new HtmlDummyColumn());
            }
            if (CoverImage::isBrowserSupported()) {
                $column = new HtmlCoverArtColumn();
                $column->addCssElement("vertical-align", "top");
                $htmlTable->addColumn(self::EXTENDED_INFO_ROW, $column);
            }
            $column = new HtmlCommentsColumn(2);
            $column->addCssElement("vertical-align", "top");
            $htmlTable->addColumn(self::EXTENDED_INFO_ROW, $column);
        }
    }

    private function initializeNoItemsRow(MusicTag $musicTag, HtmlTable $htmlTable) {
        $noItemsColumn = new HtmlLiteralColumn("", JText::_("PLG_MP3BROWSER_NOITEMS"));
        $htmlTable->addColumn(self::NO_ITEMS_ROW, $noItemsColumn);
    }

    private function initializeDefaultColumns(MusicTag $musicTag, HtmlTable $htmlTable) {
        if ($musicTag->getConfiguration()->isShowDownload()) {
            $htmlTable->addColumn(self::DEFAULT_ROW, new HtmlDownloadColumn($musicTag->getConfiguration()));
        }
        $column = new HtmlNameColumn(2);
        $htmlTable->addColumn(self::DEFAULT_ROW, $column);
        if (!$musicTag->getConfiguration()->isShowDownload()) {
            // dirty hack, immitating legacy code
            $column->addCssElement("padding-left", "10px", true);
            $column->addCssElement("padding-left", "10px");
        }
        $htmlTable->addColumn(self::DEFAULT_ROW, new HtmlPlayerColumn($musicTag->getConfiguration()));
        if ($musicTag->getConfiguration()->isShowSize()) {
            $column = new HtmlSimpleColumn(JText::_("PLG_MP3BROWSER_HEADER_SIZE"), "getFileSize");
            $column->addCssElement("width", "60px", true);
            $htmlTable->addColumn(self::DEFAULT_ROW, $column);
        }
        if ($musicTag->getConfiguration()->isShowLength()) {
            $column = new HtmlSimpleColumn(JText::_("PLG_MP3BROWSER_HEADER_DURATION"), "getPlayTime");
            $column->addCssElement("width", "70px", true);
            $htmlTable->addColumn(self::DEFAULT_ROW, $column);
        }
    }

    /**
     * Handle a single music folder.
     * @param MusicTag $musicTag music tag to fill table for
     * @param MusicFolder $musicFolder music folder to fill table for
     * @param HtmlTable $htmlTable table to write to
     * @return boolean whether any relevant rows were written to the table
     */
    private function handleSingleMusicFolder(MusicTag $musicTag, MusicFolder $musicFolder, HtmlTable $htmlTable) {
        if ($musicFolder->isExists()) {
            $sortByAsc = $musicTag->getConfiguration()->isSortByAsc();
            $maxRows = $musicTag->getConfiguration()->getMaxRows();
            $offset = $musicTag->getOffset();
            $page = $musicTag->getPageNumber();
            $totaloffset = $page * $maxRows + $offset;
            $musicItems = $musicFolder->getMusicItems($sortByAsc, $maxRows, $totaloffset);

            for ($count = 0; $count < count($musicItems); $count++) {
                $musicItem = $musicItems[$count];
                $htmlTable->addData(array(self::DEFAULT_ROW, self::EXTENDED_INFO_ROW), $musicItem);
            }
            return count($musicItems) > 0;
        }
        return false;
    }

    public function getHtmlTableForMusicTag(MusicTag $musicTag) {
        $htmlTable = new HtmlTable($musicTag->getConfiguration());
        $this->initializeDefaultColumns($musicTag, $htmlTable);
        $this->initializeExtendedInfoColumns($musicTag, $htmlTable);
        $this->initializeNoItemsRow($musicTag, $htmlTable);
        return $htmlTable;
    }

}