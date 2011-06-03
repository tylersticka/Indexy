Indexy - Themeable PHP Directory Listings
=========================================

Why settle for the same old tired default directory listings? Indexy makes them beautiful, readable and accessible. [View the demo &#8594;](http://indexy.tylersticka.com/demo/)

Features
--------

* Simple theming using [Mustache](http://mustache.github.com/) templates
* Default theme uses CSS3 media queries for tablet and smartphone viewing
* Optional [Markdown](http://daringfireball.net/projects/markdown/) viewer



Authors
-------

* [Tyler Sticka](http://tylersticka.com)

Installation
------------

**Proceed with caution!** Indexy is brand new and has not been extensively tested. It probably won't play nice co-existing with other scripts in the same directory that use .htaccess. It can also be a security risk to expose the contents of your server's directory.

1. Copy the indexy folder, .htaccess file and index.php into the root of your public directory tree.

2. Comment or un-comment any PHP5 lines in the .htaccess file to make sure your server turns on PHP5 for Indexy directories. If your server turns on PHP5 by default, you can comment all of those lines.

3. Edit your configuration options in indexy/config.php, especially the 'root_path' option if you're installing in a folder other than the root of your domain or subdomain.

4. Give it a spin!

License
-------

This software is free to use and modify. You may not charge for or sell this software, nor any derivation of it.

While it's not required, letting me know about modifications you've made proves that you are, in fact, a really nice person.

Indexy makes use of several open source libraries which may come with their own licensing rules.

Changelog
---------

* **v0.3** Re-write of 99% of the code, switched to Mustache for templating, added markdown viewer
* **v0.2** Support for non-root installations, error pages now in default theme, lots of bug fixes
* **v0.1** Original release