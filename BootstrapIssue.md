If your template is Bootstrap-based, column headers and their columns may get misaligned.

Adding the following to the template fixed this for a number of users already:

```
/* fix for mp3 browser table */
.row:before, .row:after {
  content: none;
}
```

Also see [issue 22](https://code.google.com/p/mp3-browser/issues/detail?id=22), [issue 33](https://code.google.com/p/mp3-browser/issues/detail?id=33), etc.