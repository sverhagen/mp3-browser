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
defined("_JEXEC") or die("Restricted access");

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

require_once(JPATH_PLUGINS . DS . "content" . DS . "mp3browser" . DS . "Configuration.php");
require_once(JPATH_PLUGINS . DS . "content" . DS . "mp3browser" . DS . "MusicFolder.php");
require_once(JPATH_PLUGINS . DS . "content" . DS . "mp3browser" . DS . "MusicTagsHelper.php");

/**
 * The following little class override allows us to reset individual taxonomy
 * items even though the FinderIndexerResult class does not offer us that
 * functionality.
 */
class ResetFinderIndexerResult extends FinderIndexerResult {

    public static function resetTaxonomy(FinderIndexerResult $item, $branch) {
        unset($item->taxonomy[$branch]);
    }

}

/**
 * Finder adapter for MP3 content of mp3 Browser in com_content. Adds each MP3
 * of each content item as a separate index item.
 * 
 * It would have been great to sub-class plgFinderContent. However, JDispatcher
 * thinks that plgFinderContent itself *and* anything inherited from
 * plgFinderContent are the same thing (it uses instanceof) and will thus not
 * dispatch the appropriate events to all of them. Hence, I've ripped off the
 * entire contents of plgFinderContent.
 * 
 * Another important facet of this child implementation is that, whereas
 * plgFinderContent and FinderIndexerAdapter are designed to 1:1 link an index
 * item to a content item, this child implementation creates a bunch of index
 * items, one for each MP3 of a content item. As such getUrl(...), change(...)
 * and remove($id) of FinderIndexerAdapter were overridden to search for
 * multiple link items (and delete/change them) using:
 *  "url LIKE '...%'"
 * rather than:
 *  "url = '...'".
 *
 * Yet another important facet is that is seems absolutely critical that each
 * search item has a unique URL. Because the 1:n link mentioned above, as well
 * as to avoid clashing with index items generated by the actual
 * plgFinderContent instance (that also indexes com_content content items), the
 * URL of subsequent index items is appended with the anchor for each MP3.
 * 
 * @package     Joomla.Plugin
 * @subpackage  Finder.Content
 * @since       2.5
 */
class plgFinderMp3smartsearch extends FinderIndexerAdapter {

    protected $configuration;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'mp3browser';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_content';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'mp3';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'MP3';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__content';

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState($extension, $pks, $value) {
        // Make sure we're handling com_content categories
        if ($extension == 'com_content') {
            $this->categoryStateChange($pks, $value);
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   JTable  $table    A JTable object containing the record to be deleted
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterDelete($context, $table) {
        if ($context == 'com_content.article') {
            $id = $table->id;
        } elseif ($context == 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return true;
        }
        // Remove the items.
        return $this->remove($id);
    }

    /**
     * Method to determine if the access level of an item changed.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   JTable   $row      A JTable object
     * @param   boolean  $isNew    If the content has just been created
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew) {
        // We only want to handle articles here
        if ($context == 'com_content.article' || $context == 'com_content.form') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $id = $row->id;
            $this->remove($id);
            $this->reindex($id);
        }

        // Check for access changes in the category
        if ($context == 'com_categories.category') {
            // Check if the access levels are different
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }

        return true;
    }

    /**
     * Method to reindex the link information for an item that has been saved.
     * This event is fired before the data is actually saved so we are going
     * to queue the item to be indexed later.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   JTable   $row     A JTable object
     * @param   boolean  $isNew    If the content is just about to be created
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew) {
        // We only want to handle articles here
        if ($context == 'com_content.article' || $context == 'com_content.form') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category
        if ($context == 'com_categories.category') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkCategoryAccess($row);
            }
        }

        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      A list of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($context, $pks, $value) {
        // We only want to handle articles here
        if ($context == 'com_content.article' || $context == 'com_content.form') {
            $this->itemStateChange($pks, $value);
        }
        // Handle when the plugin is disabled
        if ($context == 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
     * @param   string               $format  The item format
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function index(FinderIndexerResult $item, $format = 'html') {
        // Check if the extension is enabled
        if (JComponentHelper::isEnabled($this->extension) == false) {
            return;
        }

        // Initialize the item parameters.
        $registry = new JRegistry;
        $registry->loadString($item->params);
        $item->params = JComponentHelper::getParams('com_content', true);
        $item->params->merge($registry);

        $registry = new JRegistry;
        $registry->loadString($item->metadata);
        $item->metadata = $registry;

        // Trigger the onContentPrepare event.
        $item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
        $item->body = FinderIndexerHelper::prepareContent($item->body, $item->params);

        // Build the necessary route and path information.
        // Note the parent:: -- we don't need the hacky override we made
        $item->url = parent::getURL($item->id, $this->extension, $this->layout);
        $item->route = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug);
        $item->path = FinderIndexerHelper::getContentPath($item->route);

        // Add the meta-author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the meta-data processing instructions.
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'MP3');

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
        }

        // Add the category taxonomy data.
        $item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        $this->indexArticle($item);
    }

    private function indexArticle(FinderIndexerResult &$item) {
        $this->setLanguage($item->language);
        $musicTags = MusicTagsHelper::getMusicTagsFromSummaryBodyItem($item);
        foreach ($musicTags as $musicTag) {
            $this->indexMusicTag($item, $musicTag);
        }
    }

    private function indexMusicTag(FinderIndexerResult &$item, MusicTag $musicTag) {
        $musicTag->addConfiguration($this->configuration);
        $musicFolder = new MusicFolder($musicTag);
        if ($musicFolder->isExists()) {
            $sortByAsc = $musicTag->getConfiguration()->isSortByAsc();
            $maxRows = $musicTag->getConfiguration()->getMaxRows();
            $offset = $musicTag->getOffset();
            $page = $musicTag->getPageNumber();
            $totaloffset = $page * $maxRows + $offset;
            $musicItems = $musicFolder->getMusicItems($sortByAsc, $maxRows, $totaloffset);

            $baseUrl = $item->url . "#";
            foreach ($musicItems as $musicItem) {
                $this->indexMusicItem($item, $baseUrl, $musicItem);
            }
        }
    }

    private function setLanguage($language) {
        $lang = JFactory::getLanguage();
        $lang->load("plg_content_mp3browser", JPATH_ADMINISTRATOR, $language, true);
    }

    private function indexMusicItem(FinderIndexerResult $item, $baseUrl, MusicItem $musicItem) {
        $this->addProperties($item, $baseUrl, $musicItem);
        $this->addTaxonomyArtist($item, $musicItem);
        $this->addTaxonomyCoverArt($item, $musicItem);
        FinderIndexer::index($item);
    }

    private function addTaxonomyArtist(FinderIndexerResult $item, MusicItem $musicItem) {
        ResetFinderIndexerResult::resetTaxonomy($item, "artist");
        $artist = $musicItem->getArtist();
        if ($artist) {
            $item->addTaxonomy("artist", $artist);
        } else {
            $item->addTaxonomy("artist", "no artist");
        }
    }

    private function addTaxonomyCoverArt(FinderIndexerResult $item, MusicItem $musicItem) {
        ResetFinderIndexerResult::resetTaxonomy($item, "cover art");
        $item->addTaxonomy("cover art", $musicItem->hasCover() ? "yes" : "no");
    }

    private function addProperties(FinderIndexerResult $item, $baseUrl, MusicItem $musicItem) {
        $item->url = $baseUrl . urlencode($musicItem->getCdataName());
        $item->title = $musicItem->getTitle();
        $summary = $this->getSummary($musicItem);
        $item->summary = $summary;
        $item->body = $summary;
    }

    private function getSummary($musicItem) {
        $summary = JText::_("PLG_MP3BROWSER_HEADER_TITLE");
        $summary .= ": \"";
        $summary .= $musicItem->getTitle();
        $summary .= "\"";
        $artist = $musicItem->getArtist();
        if ($artist) {
            $summary .= " &mdash; ";
            $summary .= JText::_("PLG_MP3BROWSER_HEADER_ARTIST");
            $summary .= ": \"";
            $summary .= $artist;
            $summary .= "\"";
        }
        $comments = $musicItem->getComments();
        if ($comments) {
            $summary .= " &mdash; ";
            $summary .= JText::_("PLG_MP3BROWSER_HEADER_COMMENTS");
            $summary .= ": ";
            $summary .= $comments;
        }
        return $summary;
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup() {
        // Load dependent classes.
        include_once JPATH_SITE . '/components/com_content/helpers/route.php';

        $this->language = JFactory::getLanguage();

        $plugin = & JPluginHelper::getPlugin("content", "mp3browser");
        $pluginParams = new JRegistry();
        $pluginParams->loadString($plugin->params);
        $this->configuration = new Configuration($pluginParams);

        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $sql  A JDatabaseQuery object or null.
     *
     * @return  JDatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($sql = null) {
        $db = JFactory::getDbo();
        // Check if we can use the supplied SQL query.
        $sql = $sql instanceof JDatabaseQuery ? $sql : $db->getQuery(true);
        $sql->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
        $sql->select('a.state, a.catid, a.created AS start_date, a.created_by');
        $sql->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params');
        $sql->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering');
        $sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
        $sql->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $sql->charLength('a.alias');
        $case_when_item_alias .= ' THEN ';
        $a_id = $sql->castAsChar('a.id');
        $case_when_item_alias .= $sql->concatenate(array($a_id, 'a.alias'), ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $sql->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $sql->charLength('c.alias');
        $case_when_category_alias .= ' THEN ';
        $c_id = $sql->castAsChar('c.id');
        $case_when_category_alias .= $sql->concatenate(array($c_id, 'c.alias'), ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $sql->select($case_when_category_alias);

        $sql->select('u.name AS author');
        $sql->from('#__content AS a');
        $sql->join('LEFT', '#__categories AS c ON c.id = a.catid');
        $sql->join('LEFT', '#__users AS u ON u.id = a.created_by');

        // this is specific for this override of plgFinderContent, since we're
        // really only interested in music tags
        // note how we search for a closing tag, since the opening tag may
        // contain all sorts of parameters; this is just a simpler query
        // note that this method is also called for getting individual items,
        // hence the ( ... OR ... ) brackets, not to confuse further WHEREs
        $sql->where("(a.introtext LIKE '%{/music}%' OR a.fulltext LIKE '%{/music}%')");

        return $sql;
    }

    protected function getURL($id, $extension, $view) {
        return parent::getUrl($id, $extension, $view) . "#%";
    }

    /**
     * Method to change the value of a content item's property in the links
     * table. This is used to synchronize published and access states that
     * are changed when not editing an item directly.
     *
     * @param   string   $id        The ID of the item to change.
     * @param   string   $property  The property that is being changed.
     * @param   integer  $value     The new value of that property.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws	Exception on database error.
     */
    protected function change($id, $property, $value) {
        JLog::add('FinderIndexerAdapter::change', JLog::INFO);

        // Check for a property we know how to handle.
        if ($property !== 'state' && $property !== 'access') {
            return true;
        }

        // Get the url for the content id.
        $item = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

        // Update the content items.
        $query = $this->db->getQuery(true);
        $query->update($this->db->quoteName('#__finder_links'));
        $query->set($this->db->quoteName($property) . ' = ' . (int) $value);
        $query->where($this->db->quoteName('url') . ' LIKE ' . $item);
        $this->db->setQuery($query);
        $this->db->query();

        // Check for a database error.
        if ($this->db->getErrorNum()) {
            // Throw database error exception.
            throw new Exception($this->db->getErrorMsg(), 500);
        }

        return true;
    }

    /**
     * Method to remove an item from the index.
     *
     * @param   string  $id  The ID of the item to remove.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function remove($id) {
        JLog::add('FinderIndexerAdapter::remove', JLog::INFO);

        // Get the item's URL
        $url = $this->db->quote($this->getUrl($id, $this->extension, $this->layout));

        // Get the link ids for the content items.
        $query = $this->db->getQuery(true);
        $query->select($this->db->quoteName('link_id'));
        $query->from($this->db->quoteName('#__finder_links'));
        $query->where($this->db->quoteName('url') . ' LIKE ' . $url);

        $this->db->setQuery($query);
        $items = $this->db->loadColumn();

        // Check for a database error.
        if ($this->db->getErrorNum()) {
            // Throw database error exception.
            throw new Exception($this->db->getErrorMsg(), 500);
        }

        // Check the items.
        if (empty($items)) {
            return true;
        }

        // Remove the items.
        foreach ($items as $item) {
            FinderIndexer::remove($item);
        }

        return true;
    }

    /**
     * Method to reindex an item.
     *
     * @param   integer  $id  The ID of the item to reindex.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function reindex($id) {
        // Run the setup method.
        $this->setup();

        // Get the item.
        $item = $this->getItem($id);

        if ($item instanceof FinderIndexerResult) {
            // Index the item.
            $this->index($item);
        }
    }

}