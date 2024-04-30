<?php
/**
 * Plugin Name: Salamander Cleanup and Optimize WordPress
 * Description: This plugin removes unnecessary meta tags, disables emojis, and disables email notifications for updates.
 * Version:     1.0.0
 * Author:      gillespieza
 * Author URI:  https://github.com/gillespieza
 * License:     GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @since       1.0.0
 * @package     salamander-cleanup
 */

// Security Check: Prevent this file from being executed outside the WordPress context.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Removes emojis from front-end, back-end, RSS feeds, embeds, emails, etc.
 *
 * This function removes the emoji support injected into various places, for supporting
 * _Japanese_ characters and emojis.
 *
 * __Note:__ This function is added to the
 * {@link https://developer.wordpress.org/reference/hooks/init/ `init` hook},
 * which fires after WordPress has finished loading but before any headers are sent.
 *
 * @uses salamander_disable_emojis_tinymce()
 * @uses salamander_disable_emojis_remove_dns_prefetch()
 * @return void
 */
function salamander_disable_emojis() {
	// Prevent Emoji from loading on the front-end.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	// Remove from admin area also.
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	// Remove from RSS feeds also.
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

	// Remove from Embeds.
	remove_filter( 'embed_head', 'print_emoji_detection_script' );

	// Remove from emails.
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Disable from TinyMCE editor. Currently disabled in block editor by default.
	add_filter( 'tiny_mce_plugins', 'salamander_disable_emojis_tinymce' );

	// Don't bother prefetching DNS for this.
	add_filter( 'wp_resource_hints', 'salamander_disable_emojis_remove_dns_prefetch', 10, 2 );

	// Finally, prevent character conversion (otherwise emojis still work if available on user's device).
	add_filter( 'option_use_smilies', '__return_false' );
}
add_action( 'init', 'salamander_disable_emojis' );


/**
 * Removes the emoji plugin from TinyMCE.
 *
 * @used-by salamander_disable_emojis()
 * @param array $plugins The array of current plugins.
 * @return array         The difference betwen the two arrays.
 */
function salamander_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}


/**
 * Removes the emoji CDN hostname from DNS prefetching hints.
 *
 * @used-by salamander_disable_emojis()
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array $urls          The difference betwen the two arrays.
 */
function salamander_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
		$urls          = array_diff( $urls, array( $emoji_svg_url ) );
	}
	return $urls;
}
add_filter( 'emoji_svg_url', '__return_false' );


/**
 * Removes superfluous meta from the head.
 *
 * Removes meta tags such as adjacent post links, various RSS feed links, the
 * Windows Live Writer Manifest meta tag, the WordPress version number tag, and
 * various other unneeded tags from the `<head>` of the HTML document.
 *
 * __Note:__ We hook this into {@link https://developer.wordpress.org/reference/hooks/after_setup_theme/ `after_setup_theme`}
 * action which is called during each page load after the theme is initialized.
 * This ensures that the callback we are removing has actually been added before
 * you try to remove it.
 */
function salamander_remove_header_meta() {
	// for WordPress >= 3.0 adjacent posts.
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	// Category feeds.
	remove_action( 'wp_head', 'feed_links_extra', 3 );

	// Post and Comment Feed.
	remove_action( 'wp_head', 'feed_links', 2 );

	// index link.
	remove_action( 'wp_head', 'index_rel_link' );

	// prev link.
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );

	// remove REST API link.
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

	// removes the REST API from HTTP headers.
	remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

	// EditURI link/Weblog Client Link.
	remove_action( 'wp_head', 'rsd_link' );

	// start link.
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );

	// Windows Live Writer Manifest.
	remove_action( 'wp_head', 'wlwmanifest_link' );

	// WordPress Version - security risk.
	remove_action( 'wp_head', 'wp_generator' );

	// removes api.w.org relation link.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

	// remove shortlinks.
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'after_setup_theme', 'salamander_remove_header_meta' );


/**
 * Removes the WordPress generator number.
 *
 * Removes the WordPress generator number from the RSS feed as this is a security
 * risk. Appears as `<meta name="generator" content="WordPress 4.9.2">` in the head.
 *
 * @return empty string.
 */
function salamander_remove_generator_version() {
	return '';
}
add_filter( 'the_generator', 'salamander_remove_generator_version' );


/**
 * Remove the inline CSS that the gallery shortcode outputs - this is invalid
 * HTML, and we should put that CSS in the stylesheet instead.
 */
add_filter( 'use_default_gallery_style', '__return_false' );


/**
 * Disable successful update notices.
 *
 * Adds a filter to disable email notifications after updates.
 */
function itc_stop_update_emails( $send, $type, $core_update, $result ) {
	if ( ! empty( $type ) && 'success' === $type ) {
		return false;
	}
	return true;
}
add_filter( 'auto_core_update_send_email', 'itc_stop_update_emails', 10, 4 ); // after core updates.

// Disable auto-update email notifications for plugins.
add_filter( 'auto_plugin_update_send_email', '__return_false' );

// Disable auto-update email notifications for themes.
add_filter( 'auto_theme_update_send_email', '__return_false' );
