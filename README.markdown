Indexy - Themeable PHP Directory Listings
=========================================

Indexy replaces your tired, old default directory listings with a beautiful, readable and accessible new layout.

It uses [Dwoo](http://dwoo.org/) for templates. Dwoo is (almost) fully compatible with Smarty templates and plugins, while adding native PHP5 support and many features.

The default theme uses CSS3 media queries to respond thoughtfully to different devices.

Indexy is very young, so any and all improvements and enhancements are encouraged. I'm mostly a web designer who casually delves into the murkier parts of server-side development, so I'd sincerely love the help and the chance to learn from talented contributors.

Authors
-------

* [Tyler Sticka](http://tylersticka.com)

Installation
---------------

**Proceed with caution!** Indexy is brand new and has not been extensively tested. It probably won't play nice co-existing with other scripts in the same directory that use .htaccess. It can also be a security risk to expose the contents of your server's directory.

1. Copy the indexy folder, .htaccess file and index.php into the root of your public directory tree.

2. Comment or un-comment any PHP5 lines in the .htaccess file to make sure your server turns on PHP5 for Indexy directories. If your server turns on PHP5 by default, you can comment all of those lines.

3. Edit your configuration options in indexy/config.php, especially the 'root_path' option if you're installing in a folder other than the root of your domain or subdomain.

4. Give it a spin!

If you don't want search engines scraping the contents of your listing, you should create a [robots.txt](http://www.robotstxt.org/) file at the root of your installation.

License
-------

This software is free to use and modify. You may not charge for or sell this software, nor any derivation of it.

While it's not required, letting me know about modifications you've made proves that you are, in fact, a really nice person.

Indexy includes the [Dwoo](http://dwoo.org/) template engine. Portions of the script that rely on it are subject to Dwoo's licensing restrictions.

Changelog
---------

* **v0.2** Support for non-root installations, error pages now in default theme, lots of bug fixes
* **v0.1** Original release