# Limitations #

It is important to understand that this a plugin, not a component, and as such it is unaware of pagination (although this can be [mimicked](Pagination.md)) or where it is being used.

It is also important to understand that this plugin is directly based upon the contents of a file system folder, and as such does a large amount of file I/O, which negatively affects performance and which may not be what you want for your application.

As a result of the latter limitation, search results are only updated when changing the article or reindexing. Changes to the contents of the MP3 file system folder are not picked up automatically.

These are mostly fundamental limitations; if they affect your application, this plugin may simply not be for you.

Only tested with Joomla 2.5.