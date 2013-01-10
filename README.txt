mp3 Browser

This plugin will create a table of every MP3 in a specified folder. It displays the ID3 information of each track with a link to download or play the file in the browser. Shameless fork from dotcomdevelopment.com.

You may use Maven (3.0.4 or higher) to build this project. See e.g. http://maven.apache.org/run-maven/index.html

I am aware that Maven may be uncommon in PHP and Joomla plugin projects, but it works great for me in building the output files.

Having installed and set up Maven you would do as follows:

	...\mp3-Browser> mvn install

In case of BUILD SUCCESS, you should find package archives in target/pkg_mp3browser-0.2.4-SNAPSHOT.zip (and .tar.gz, .tar.bz2) that can be installed in Joomla. It contains the multiple plugins.

You can use Maven release to release the project. It will nicely label a version number and upload package archives to Google Code:

	...\mp3-Browser> mvn release:prepare release:perform

For the latter to work, you need Google Code in your settings.xml of Maven:

	<server>
		<id>googlecode</id>
		<username>...</username>
		<password>...</password>
	</server>