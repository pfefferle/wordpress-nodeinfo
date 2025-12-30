# NodeInfo(2)

- Contributors: pfefferle
- Donate link: https://notiz.blog/donate/
- Tags: nodeinfo, fediverse, ostatus, diaspora, activitypub
- Requires at least: 6.6
- Tested up to: 6.9
- Stable tag: 3.1.0
- Requires PHP: 7.2
- License: MIT
- License URI: https://opensource.org/licenses/MIT

NodeInfo and NodeInfo2 for WordPress!

## Description

[NodeInfo](http://nodeinfo.diaspora.software/) is an effort to create a standardized way of exposing metadata about a server running one of the distributed social networks. The two key goals are being able to get better insights into the user base of distributed social networking and the ability to build tools that allow users to choose the best fitting software and server for their needs.

This plugin provides a barebone JSON file with basic "node"-informations. The file can be extended by other WordPress plugins, like [OStatus](https://wordpress.org/plugins/ostatus-for-wordpress/), [Diaspora](https://github.com/pfefferle/wordpress-dandelion) or [ActivityPub](https://wordpress.org/plugins/activitypub/)/[Pterotype](https://wordpress.org/plugins/pterotype/).

### What information does this plugin share?

The plugin exposes the following public information about your site:

* **Software**: WordPress version (major version only for privacy)
* **Usage statistics**: Number of users, posts, and comments
* **Site info**: Your site name and description
* **Protocols**: Which federation protocols your site supports (e.g., ActivityPub)
* **Services**: Which external services your site can connect to (e.g., RSS feeds)

This information helps other servers in the Fediverse discover and interact with your site.

### Supported NodeInfo versions

This plugin supports all major NodeInfo specification versions:

* **NodeInfo 1.0** and **1.1** - Original specifications
* **NodeInfo 2.0**, **2.1**, and **2.2** - Current specifications with extended metadata
* **NodeInfo2** - Alternative single-endpoint format

### Endpoints

After activation, the following endpoints become available:

* `/.well-known/nodeinfo` - Discovery document (start here)
* `/wp-json/nodeinfo/2.2` - NodeInfo 2.2 (recommended)
* `/wp-json/nodeinfo/2.1` - NodeInfo 2.1
* `/wp-json/nodeinfo/2.0` - NodeInfo 2.0
* `/wp-json/nodeinfo/1.1` - NodeInfo 1.1
* `/wp-json/nodeinfo/1.0` - NodeInfo 1.0
* `/.well-known/x-nodeinfo2` - NodeInfo2 format

## Frequently Asked Questions

### Why do I need this plugin?

If you want your WordPress site to be part of the Fediverse (decentralized social networks like Mastodon), this plugin helps other servers discover information about your site. It works together with plugins like [ActivityPub](https://wordpress.org/plugins/activitypub/) to make your site fully federated.

### Is any private information shared?

No. Only public information about your site is shared, such as your site name, description, and post counts. No personal user data or private content is exposed.

### How can I verify it's working?

Visit `https://yoursite.com/.well-known/nodeinfo` in your browser. You should see a JSON document with links to the NodeInfo endpoints.

### Can other plugins extend the NodeInfo data?

Yes! This plugin is designed to be extensible. Other plugins can use WordPress filters to add their own protocols, services, or metadata. For example, the ActivityPub plugin automatically adds `activitypub` to the supported protocols list.

### How do I know if everything is configured correctly?

Go to **Tools > Site Health** in your WordPress admin. The plugin adds two health checks:

* **NodeInfo Well-Known Endpoint** - Verifies that `/.well-known/nodeinfo` is accessible
* **NodeInfo REST Endpoint** - Verifies that the NodeInfo 2.2 REST endpoint returns valid data

If either check fails, you'll see recommendations on how to fix the issue.

## Changelog

Project and support maintained on github at [pfefferle/wordpress-nodeinfo](https://github.com/pfefferle/wordpress-nodeinfo).

### 3.1.0

* Added singleton-based plugin loading mechanism for better extensibility
* Added backwards compatibility handler for deprecated `wellknown_nodeinfo_data` filter

### 3.0.0

* Refactored to filter-based architecture for better extensibility
* Added support for NodeInfo 2.2
* Added separate integration classes for each NodeInfo version (1.0, 1.1, 2.0, 2.1, 2.2)
* Added PSR-4 style autoloader
* Updated schemas to match official NodeInfo specifications with enums and constraints
* Added `nodeinfo_protocols` filter for plugins to register protocols
* Added `software.homepage` field for NodeInfo 2.1 and 2.2
* Added Site Health checks to verify endpoints are accessible

### 2.3.1

* mask version number

### 2.3.0

* add nodeName, nodeDescription and nodeIcon to meta array

### 2.2.0

* add MAUs

### 2.1.1

* load plugin on init, to keep up with changes on the ActivityPub side

### 2.1.0

* count only users that can "publish_posts"

### 2.0.0

* removed support for ServiceInfo, as it never caught on

### 1.0.8

* fix link to WordPress repository (props @jeherve)
* add generator object to metadata to link to plugin repository

### 1.0.7

* NodeInfo 2.1 protocols field has to be an array, not an object

### 1.0.6

* add autodiscovery link for nodeinfo 2.1
* fix some typos/copy&paste issues

### 1.0.5

* fix missing permission_callback issue

### 1.0.4

* fixed whitespace problem

### 1.0.3

* added admin_email to metadata, to be able to "Manage your instance" on https://fediverse.network/manage/

### 1.0.2

* fixed JSON schema (thanks @hrefhref)

### 1.0.1

* use `home_url` insted of `site_url`

### 1.0.0

* initial

## Installation

Follow the normal instructions for [installing WordPress plugins](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### Automatic Plugin Installation

To add a WordPress Plugin using the [built-in plugin installer](https://codex.wordpress.org/Administration_Screens#Add_New_Plugins):

1. Go to [Plugins](https://codex.wordpress.org/Administration_Screens#Plugins) > [Add New](https://codex.wordpress.org/Plugins_Add_New_Screen).
1. Type "`nodeinfo`" into the **Search Plugins** box.
1. Find the WordPress Plugin you wish to install.
    1. Click **Details** for more information about the Plugin and instructions you may wish to print or save to help setup the Plugin.
    1. Click **Install Now** to install the WordPress Plugin.
1. The resulting installation screen will list the installation as successful or note any problems during the install.
1. If successful, click **Activate Plugin** to activate it, or **Return to Plugin Installer** for further actions.

### Manual Plugin Installation

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
