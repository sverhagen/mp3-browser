You may want to install an alternative player to the Flash player that has long been our default:

  * Using [MediaElement.js](http://mediaelementjs.com/) and pretty much followed the steps as described there

  * Add the mediaelement-and-player.min.js and mediaelementplayer.css to my site's head (I actually used the [HeadTag plugin](http://extensions.joomla.org/extensions/core-enhancements/coding-a-scripts-integration/head-code/23718) to do this specifically for pages that I was interested in and so that I did not have to hack my template, but I'm sure other ways will work too)

  * Replace the "Player source code" (mp3 Browser, Advanced Options) with the following:

```
<audio controls>
  <source src="%3" type="audio/mpeg">
Your browser does not support the audio element.
</audio> 
```