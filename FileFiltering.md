# File Filtering #

The files can be filtered on a regular expression, such as: `+\.mp3`, `.*hip hop.*\.mp3` for all MP3s with "hip hop" in their name, or _filename_ to display a specific single file name.

Don't forget that this uses regular expressions: a specific single file name may thus be filtered on as follows:

`\(Feels Like\) Heaven - Fiction Factory\.MP3`

The regular expression is applied case-insensitively.

You can also use file filtering to [support other file types](http://code.google.com/p/mp3-browser/issues/detail?id=34):

`+\.(mp3|flac|occ)`

But be aware that the standard Flash player may not support this ([workaround](AlternativePlayer.md)).