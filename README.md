# salamander-cleanup
=== Salamander Cleanup and Optimize WordPress ===
Contributors: gillespieza
Tags: optimisation,snippet
Requires at least: 6.1
Tested up to: 6.4.3
Requires PHP: 7.4
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Removes unnecessary meta, disables emojis, and disables email notifications for updates.

== Description ==
*NOTE:* This isn't really a "proper" plugin - it's really just a collection of snippets that I find useful in almost every WordPress site I set up.

This WordPress plugin removes unnecessary meta tags, disables emojis, and disables email notifications for updates. It also removes various links in the document head, which you might actually want to keep (eg, RSS feeds), depending on your business needs. The following functions are implemented:

## Disable Emojis
- Disable emojis from being autoloaded on the front-end, back-end, in RSS feeds, embeds, emails, etc. We do this because most of our clients don't write in Japanese characters or use emojis on their websites.
- We remove Emoji support from the TinyMCE editor
- We remove the emoji CDN hostname from DNS prefetching hints.

## Clean up meta
- We remove various superfluous meta tags from the document `<head>`, such as:
  - links to Adjacent Posts
  - link to the Index and Previous Posts
  - Category, Post and Comment RSS feeds (you may prefer to keep this)
  - the link to the REST API, and remove the REST API from the header (you may need to keep this)
  - EditURI link/Weblog Client Link
  - Windows Live Writer Manifest
  - the WordPress Generator number (a minor security risk) from both the html head and the RSS head
  - the api.w.org relation link
  - shortlinks

## Remove inline gallery CSS
- The inline CSS that WordPress injects into the gallery is invalid HTML, and frankly, it\'s messy. CSS should be in your `style.css` file anyway, where it is more editable.

## Disable successful update notices
- We disable email updates for successful plugin/core updates, because if you manage a lot of WordPress websites, this notification becomes noise. Ideally, we only want a notification on a failed update.
