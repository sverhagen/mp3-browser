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
require_once(__DIR__ . DS . "MusicTag.php");

class MusicTagsHelper {

    /**
     * Get music tags for the given item with body and summary.
     * @param string $item item to get music tags for, could be a FinderIndexerResult object
     * @return array(MusicTag) array of music tags
     */
    public static function getMusicTagsFromSummaryBodyItem($item) {
        $matches1 = self::getMusicTagsFromText($item->summary);
        $matches2 = self::getMusicTagsFromText($item->body);
        return array_unique(array_merge($matches1, $matches2));
    }

    /**
     * Get music tags for the given article's intro text and text.
     * @param string $article article to get music tags for
     * @return array(MusicTag) array of music tags
     */
    public static function getMusicTagsFromArticle($article) {
        $matches1 = self::getMusicTagsFromText($article->introtext);
        if (isset($article->text)) {
            $matches2 = self::getMusicTagsFromText($article->text);
            return array_unique(array_merge($matches1, $matches2));
        }
        return array_unique($matches1);
    }

    private static function getMusicTagsFromText($text) {
        preg_match_all(MUSIC_PATTERN, $text, $matches, PREG_PATTERN_ORDER);
        $results = array();
        foreach ($matches[0] as $match) {
            $results[] = new MusicTag($match);
        }
        return $results;
    }

    /**
     * Replace the given music tag in the given article's intro text and text.
     * @param type $article article to replace music tags in
     * @param MusicTag $musicTag music tag to replace in article
     */
    public static function replaceTagsWithReplacementContent($article, MusicTag $musicTag) {
        $search = $musicTag->getFullTag();
        $replace = $musicTag->getReplacementContent();
        $article->introtext = str_replace($search, $replace, $article->introtext);
        if (isset($article->text)) {
            $article->text = str_replace($search, $replace, $article->text);
        }
    }

}