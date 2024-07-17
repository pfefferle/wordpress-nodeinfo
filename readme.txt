=== NodeInfo(2) ===

Contributors: pfefferle
Donate link: https://notiz.blog/donate/
Tags: nodeinfo, fediverse, ostatus, diaspora, activitypub
Requires at least: 4.9
Tested up to: 6.6
Stable tag: 2.3.1
Requires PHP: 5.6
License: MIT
License URI: https://opensource.org/licenses/MIT

NodeInfo and NodeInfo2 for WordPress!

== Description ==

[NodeInfo](http://nodeinfo.diaspora.software/) is an effort to create a standardized way of exposing metadata about a server running one of the distributed social networks. The two key goals are being able to get better insights into the user base of distributed social networking and the ability to build tools that allow users to choose the best fitting software and server for their needs.

This plugin provides a barebone JSON file with basic "node"-informations. The file can be extended by other WordPress plugins, like [OStatus](https://wordpress.org/plugins/ostatus-for-wordpress/), [Diaspora](https://github.com/pfefferle/wordpress-dandelion) or [ActivityPub](https://wordpress.org/plugins/activitypub/)/[Pterotype](https://wordpress.org/plugins/pterotype/).

== Frequently Asked Questions ==

== Changelog ==

Project and support maintained on github at [pfefferle/wordpress-nodeinfo](https://github.com/pfefferle/wordpress-nodeinfo).

= 2.3.1 =

* mask version number

= 2.3.0 =

* add nodeName, nodeDescription and nodeIcon to meta array

= 2.2.0 =

* add MAUs

= 2.1.1 =

* load plugin on init, to keep up with changes on the ActivityPub side

= 2.1.0 =

* count only users that can "publish_posts"

= 2.0.0 =

* removed support for ServiceInfo, as it never caught on

= 1.0.8 =

* fix link to WordPress repository (props @jeherve)
* add generator object to metadata to link to plugin repository

= 1.0.7 =

* NodeInfo 2.1 protocols field has to be an array, not an object

= 1.0.6 =

* add autodiscovery link for nodeinfo 2.1
* fix some typos/copy&paste issues

= 1.0.5 =

* fix missing permission_callback issue

= 1.0.4 =

* fixed whitespace problem

= 1.0.3 =

* added admin_email to metadata, to be able to "Manage your instance" on https://fediverse.network/manage/

= 1.0.2 =

* fixed JSON schema (thanks @hrefhref)

= 1.0.1 =

* use `home_url` insted of `site_url`

= 1.0.0 =

* initial

== Installation ==

Follow the normal instructions for [installing WordPress plugins](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

= Automatic Plugin Installation =

To add a WordPress Plugin using the [built-in plugin installer](https://codex.wordpress.org/Administration_Screens#Add_New_Plugins):

1. Go to [Plugins](https://codex.wordpress.org/Administration_Screens#Plugins) > [Add New](https://codex.wordpress.org/Plugins_Add_New_Screen).
1. Type "`nodeinfo`" into the **Search Plugins** box.
1. Find the WordPress Plugin you wish to install.
    1. Click **Details** for more information about the Plugin and instructions you may wish to print or save to help setup the Plugin.
    1. Click **Install Now** to install the WordPress Plugin.
1. The resulting installation screen will list the installation as successful or note any problems during the install.
1. If successful, click **Activate Plugin** to activate it, or **Return to Plugin Installer** for further actions.

= Manual Plugin Installation =

There are a few cases when manually installing a WordPress Plugin is appropriate.

* If you wish to control the placement and the process of installing a WordPress Plugin.
* If your server does not permit automatic installation of a WordPress Plugin.
* If you want to try the [latest development version](https://github.com/pfefferle/wordpress-nodeinfo).

Installation of a WordPress Plugin manually requires FTP familiarity and the awareness that you may put your site at risk if you install a WordPress Plugin incompatible with the current version or from an unreliable source.

Backup your site completely before proceeding.

To install a WordPress Plugin manually:

* Download your WordPress Plugin to your desktop.
    * Download from [the WordPress directory](https://wordpress.org/plugins/nodeinfo/)
    * Download from [GitHub](https://github.com/pfefferle/wordpress-nodeinfo/releases)
* If downloaded as a zip archive, extract the Plugin folder to your desktop.
* With your FTP program, upload the Plugin folder to the `wp-content/plugins` folder in your WordPress directory online.
* Go to [Plugins screen](https://wordpress.org/support/article/plugins-add-new-screen/) and find the newly uploaded Plugin in the list.
* Click **Activate** to activate it.
