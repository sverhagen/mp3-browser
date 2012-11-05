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
class MusicTagsHelper {

    const MUSIC_PATTERN = "#{music}(.*?){/music}#s";

    public static function getMusicTagsFromArticle($article) {
        $matches1 = self::getMusicTagsFromText($article->introtext);
        $matches2 = self::getMusicTagsFromText($article->text);
        return array_unique(array_merge($matches1, $matches2));
    }

    private static function getMusicTagsFromText($text) {
        preg_match_all(self::MUSIC_PATTERN, $text, $matches, PREG_PATTERN_ORDER);
        return $matches[1];
    }

    public static function replaceTagsWithContent($article, $musicPathTrail, $html) {
        $tag = "#{music}" . $musicPathTrail . "{/music}#s";
        $article->introtext = preg_replace($tag, $html, $article->introtext);
        if (isset($article->text)) {
            $article->text = preg_replace($tag, $html, $article->text);
        }
    }

}