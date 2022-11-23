<?php
/**
 * Plugin Name:       Nginx Cache Cleaner
 * Plugin URI:
 * Description:		  A Wordpress plugin to clear Nginx cache
 * Requires at least:
 * Requires PHP:
 * License:			  GPLv3
 * Author:            Guilherme Almeida Girardi
 * Author URI:
 * Version:
 * Text Domain:       nginx-cache-cleaner
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function purge_zone_once() {
	static $completed = false;

	if (!$completed) {
		purge_zone();
		$completed = true;
	}
}

function purge_zone() {
	if (file_exists(NGINX_CACHE_CLEANER_PATH))
		system((NGINX_CACHE_CLEANER_ROOT ? "sudo " : "")."rm -rf ".NGINX_CACHE_CLEANER_PATH."/*");
	else
		show_editor_message("The directory informed in NGINX_CACHE_CLEANER_PATH does not exist");
}

function show_editor_message($message) {
	add_settings_error('title_long_error', '', $message, 'error');
	settings_errors('title_long_error');
}

function init() {
	// use `nginx_cache_purge_actions` filter to alter default purge actions
	$purge_actions = (array)apply_filters(
		'nginx_cache_purge_actions',
		array(
			'publish_phone', 'save_post', 'edit_post', 'delete_post', 'wp_trash_post', 'clean_post_cache',
			'trackback_post', 'pingback_post', 'comment_post', 'edit_comment', 'delete_comment', 'wp_set_comment_status',
			'switch_theme', 'wp_update_nav_menu', 'edit_user_profile_update'
		)
	);

	foreach ($purge_actions as $action) {
		if (did_action($action)) {
			purge_zone_once();
		} else {
			add_action($action, 'purge_zone_once');
		}
	}

	if (!defined('NGINX_CACHE_CLEANER_PATH'))
		show_editor_message("NGINX_CACHE_CLEANER_PATH const not defined");

	if (!defined('NGINX_CACHE_CLEANER_ROOT'))
		define('NGINX_CACHE_CLEANER_ROOT', false);
}

add_action('init', 'init', 20);
