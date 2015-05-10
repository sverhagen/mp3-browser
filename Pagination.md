# Pagination #

The page number can be included as a parameter in the music tag, such as: `{music pageNumber="number"}`. The page number is 0-based. Page 0 is the default if this parameter is not included in the music tag. The page number is used to determine which music files to display, using the `maxRows` [configuration](Configuration.md) option.

[Here](http://code.google.com/p/mp3-browser/issues/detail?id=30) is some more information about pagination.

The page number is not to be confused with the [offset](Offset.md).