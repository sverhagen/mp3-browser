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
 * Copyright 2012-'13 Totaal Software (www.totaalsoftware.com).
 */
defined("_JEXEC") or die("Restricted access");

jimport("joomla.plugin.plugin");

require_once(dirname(__FILE__) . DS . "Configuration.php");
require_once(dirname(__FILE__) . DS . "CoverImage.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "AbstractHtmlTable.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlCoverArtColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlCommentsColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlDivTable.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlDownloadColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlDummyColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlLiteralColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlNameColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlPlayerColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlSimpleColumn.php");
require_once(dirname(__FILE__) . DS . "html" . DS . "HtmlTable.php");
require_once(dirname(__FILE__) . DS . "MusicFolder.php");
require_once(dirname(__FILE__) . DS . "MusicTagsHelper.php");
require_once(dirname(__FILE__) . DS . "PluginHelper.php");

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
     * This is the first stage in preparing content for output...
     * See http://docs.joomla.org/Plugin/Events/Content#onContentPrepare
     */
    public function onContentPrepare($context, &$article, &$params, $limitstart = "") {
        $this->initializePlugin();
        $musicTags = MusicTagsHelper::getMusicTagsFromArticle($article);
        if (count($musicTags)) {
            foreach ($musicTags as $musicTag) {
                $this->handleSingleMusicTag($article, $musicTag);
            }
        }
        return "";
    }

    /**
     * Return a list of all sub-directories of the given parent directory.
     * 
     * @param type $parent parent directory to get sub-directories for
     * @param type $basepath base path to prepend instead of parent directory
     * @return array paths of sub-directories, relative to the given directory
     */
    private function getAllSubdirectories($parent, $basepath="") {
        $dir_array = array();
        if (!is_dir($parent)) {
            return $dir_array;
        }

        $directories = glob($parent . '/*' , GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $directorySegment = basename($directory);
            $dir_array[] = $basepath . $directorySegment;
            $childDir = $parent . DS . $directorySegment;
            $childPath = $basepath . $directorySegment . DS;
            $dir_array = array_merge($dir_array, $this->getAllSubdirectories($childDir, $childPath));
        }
        return $dir_array;
    }

    private function handleSingleMusicTag($article, MusicTag $musicTag) {
        $musicTag->addConfiguration($this->configuration);
        $musicFolder = new MusicFolder($musicTag);
        $htmlTableString = $this->getHtmlTableString($musicTag, $musicFolder);

        if ($musicTag->getConfiguration()->includeSubdirectories()) {
            $path = $musicTag->getPathTrail();
            // Retrieve All subdirs relative to $path...
            $directories = $this->getAllSubdirectories($path);

            foreach ($directories as $directory) {
                $musicFolder = new MusicFolder($musicTag);
                $musicFolder->setOverridePath($path . DS . $directory);
                $subHtmlTable = $this->getHtmlTableString($musicTag, $musicFolder);
                if($subHtmlTable) {
                    $title = str_replace(DS, " &mdash; ", $directory);
                    $htmlTableString = $htmlTableString . "<h3>$title</h3>" . $subHtmlTable;
                }
            }
        }
        $musicTag->setReplacementContent($htmlTableString);
        MusicTagsHelper::replaceTagsWithReplacementContent($article, $musicTag);
    }
    
    private function getHtmlTableString(MusicTag $musicTag, MusicFolder $musicFolder) {
        $htmlTable = $this->getHtmlTableForMusicTag($musicTag);
        $htmlTable->start(array(self::DEFAULT_ROW));
        $empty = !$this->handleSingleMusicFolder($musicTag, $musicFolder, $htmlTable);
        if ($empty) {
            // print empty table message
            $htmlTable->addData(array(self::NO_ITEMS_ROW), NULL);
        }
        $htmlTable->finish();
        if ($musicTag->getConfiguration()->hideEmptyTable() && $empty) {
            // no entries and empty table should not be printed, so reset the output
            return "";
        }
        return $htmlTable;
    }

    private function initializePlugin() {
        PluginHelper::loadLanguage();

        $this->configuration = new Configuration($this->params);
    }

    private function initializeExtendedInfoColumns(MusicTag $musicTag, AbstractHtmlTable $htmlTable) {
        if ($musicTag->getConfiguration()->isShowExtendedInfo()) {
            if ($this->isAllowDownload($musicTag->getConfiguration())) {
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

    private function initializeNoItemsRow(MusicTag $musicTag, AbstractHtmlTable $htmlTable) {
        $noItemsColumn = new HtmlLiteralColumn("", JText::_("PLG_MP3BROWSER_NOITEMS"));
        $htmlTable->addColumn(self::NO_ITEMS_ROW, $noItemsColumn);
    }

    private function initializeDefaultColumns(MusicTag $musicTag, AbstractHtmlTable $htmlTable) {
        if ($this->isAllowDownload($musicTag->getConfiguration())) {
            $htmlTable->addColumn(self::DEFAULT_ROW, new HtmlDownloadColumn($musicTag->getConfiguration()));
        }
        $column = new HtmlNameColumn(2);
        $htmlTable->addColumn(self::DEFAULT_ROW, $column);
        if (!$this->isAllowDownload($musicTag->getConfiguration())) {
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
    private function handleSingleMusicFolder(MusicTag $musicTag, MusicFolder $musicFolder, AbstractHtmlTable $htmlTable) {
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
        $htmlTable = $this->createHtmlTable($musicTag);
        $this->initializeDefaultColumns($musicTag, $htmlTable);
        $this->initializeExtendedInfoColumns($musicTag, $htmlTable);
        $this->initializeNoItemsRow($musicTag, $htmlTable);
        return $htmlTable;
    }

    private function isAllowDownload(Configuration $configuration) {
        if (!$configuration->isShowDownload()) {
            return false;
        }
        if (!$configuration->isLimitDownload()) {
            return true;
        }
        $user = JFactory::getUser();
        $status = $user->guest;
        return $status != 1;
    }

    private function createHtmlTable($musicTag) {
        $configuration = $musicTag->getConfiguration();
        switch ($configuration->getTableStrategy()) {
            case Configuration::TABLE_STRATEGY_DIV:
                return new HtmlDivTable($configuration);
                break;
            case Configuration::TABLE_STRATEGY_SWITCH:
                $templateId = $configuration->getTableStrategySwitchTemplate();
                if (PluginHelper::isCurrentTemplate($templateId)) {
                    return new HtmlDivTable($configuration);
                }
            case Configuration::TABLE_STRATEGY_TABLE:
            default:
                return new HtmlTable($configuration);
                break;
        }
    }

}