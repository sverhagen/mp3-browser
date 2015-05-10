# Player Code #

The HTML code that is used to render the audio player can be overridden. Doing so allows the use of alternative players to the one that's used by default.

In the configured HTML code for the player the following placeholders may be used:

| **Placeholder** | **Description** | **Example** |
|:----------------|:----------------|:------------|
| `%1` | Width | `220` |
| `%2` | Background color | `#D6E3EB` |
| `%3` | Audio file | `/images/mp3/test.mp3` |
| `%4` | File path of the default player (different depending on configured volume control). You would typically not use this placeholder when overriding the default | `http://www.example.com/plugins/content/mp3browser/dewplayer/dewplayer.swf` |

There is a [chewed out example](AlternativePlayer.md) available.