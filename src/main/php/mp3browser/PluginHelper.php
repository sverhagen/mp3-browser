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
class PluginHelper {

    public static function loadLanguage() {
        $lang = JFactory::getLanguage();
        $lang->load("plg_content_mp3browser", JPATH_ADMINISTRATOR);
    }

    public static function getPluginBaseUrl() {
        $mosConfig_live_site = JURI :: base();
        if (substr($mosConfig_live_site, -1) == "/") {
            $mosConfig_live_site = substr($mosConfig_live_site, 0, -1);
        }
        return $mosConfig_live_site . "/plugins/content/mp3browser/";
    }

    public static function isCurrentTemplate($id) {
        $name = self::getTemplateNameFromId($id);

        $app = JFactory::getApplication();
        $currentTemplateName = $app->getTemplate();

        return $currentTemplateName == $name;
    }

    public static function getTemplateNameFromId($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("template");
        $query->from("#__template_styles");
        $query->where("id = " . $id);
        $db->setQuery($query);
        $template = $db->loadObject();
        if ($template) {
            return $template->template;
        } else {
            return NULL;
        }
    }

}