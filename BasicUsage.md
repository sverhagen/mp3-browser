# Basic Usage #

[Download](http://code.google.com/p/mp3-browser/downloads/list) the package. Package installation through the administration site of your Joomla installation, of two plugins:

  * Content plugin
  * [Smart Search](http://docs.joomla.org/Smart_Search_quickstart_guide) plugin

Please remember to publish the plugins!

Place the tags `{music}folderName/subfolder{/music}` in any content item.
The plugin will strip the tags and replace it with a table containing all the MP3s from that folder for playing or downloading.

For displaying each music item the MP3's ID3 tag of title/artist is used; if no title tag is present the file name is used.

**Please note this [issue](BootstrapIssue.md) that users of Bootstrap-styling are experiencing.**