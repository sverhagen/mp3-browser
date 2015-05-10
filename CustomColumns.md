# Custom column configuration #

This is pretty advanced, but also very power- and use-ful!

If you look at the following functions in `mp3browser.php` you will see how the columns are created, and you should be able to fairly easily change this.

The default configuration has two rows for each MP3, let's call these:
  * Default row:
    * Typically simple properties such as name, length and the player
    * Can be found in function `initializeDefaultColumns`
  * Extended row:
    * Typically album art and the longer MP3 comment
    * Can be found in function `initializeExtendedInfoColumns`

The existing functions can be a bit hard to see through, because they're already parameterized with the configuration options.

But essentially there is a number of handy column types that can be used.

Add a new column to a row e.g. as follows:

```
   $column = new HtmlNameColumn(2); // this sets a colspan=2
   $htmlTable->addColumn(self::DEFAULT_ROW, $column);
```

Following is a brief list of supported column types:

| **Type name** | **Description** | **Note** |
|:--------------|:----------------|:---------|
| `HtmlCommentsColumn` | Displays the MP3 comments and copyright information | Takes `colspan` as construction parameter |
| `HtmlCoverArtColumn` | Displays the MP3 cover art | Takes `colspan` as construction parameter |
| `HtmlDownloadColumn` | Displays download button as configured | Takes `colspan` as construction parameter |
| `HtmlDummyColumn` | Shows nothing (empty) | Takes `colspan` as construction parameter |
| `HtmlLiteralColumn` | Shows text literal | Takes a header text (may be empty), a text literal and colspan as construction parameters |
| `HtmlNameColumn` | Shows basic description of MP3 (based on title and artist) | Takes `colspan` as construction parameter |
| `HtmlPlayerColumn` | Displays the configured MP3 player | Takes `colspan` as construction parameter |
| `HtmlSimpleColumn` | Displays a property of the MP3 | Takes a header text and the property function name as construction parameters. Since a function name is given, it may do more than just returning a class field; it may calculate it or whatever |

You can also go crazy and define additional rows for each MP3 fairly easily.

Also, there is a row defined that's used if there is no MP3s to show on a page; can be found in function `initializeNoItemsRow`.