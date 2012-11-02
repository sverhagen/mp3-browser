mp3 Browser

This plugin will create a table of every MP3 in a specified folder. It displays the ID3 information of each track with a link to download or play the file in the browser. Shameless fork from dotcomdevelopment.com.

You may use Maven (3.0.4 or higher) to build this project. See e.g. http://maven.apache.org/run-maven/index.html

I am aware that Maven may be uncommon in PHP and Joomla plugin projects, but it works great for me in building the output files.

Having installed and set up Maven you would do as follows:

	...\mp3-Browser> mvn install

In case of BUILD SUCCESS, you should find an executable JAR file in target/plg_mp3-Browser-0.0.1-SNAPSHOT.zip (and .tar.gz, .tar.bz2) that can be installed in Joomla.