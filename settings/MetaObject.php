<?php

namespace ZinnZWRAM;

Class MetaObject
{

	/**
	 * Gets all metadata related to the object type
	 * @param $table
	 * @return array
	 */
	public function getMetaItems($table)
	{
		global $wpdb;
		
		// Create cache key based on table name
		$cache_key = 'zinn_zwram_meta_keys_' . md5($table);
		$cached_result = wp_cache_get($cache_key);
		
		if ($cached_result !== false) {
			return $cached_result;
		}
		
		// Validate table name against whitelist
		$allowed_tables = array(
			$wpdb->postmeta,
			$wpdb->commentmeta,
			$wpdb->usermeta,
			$wpdb->termmeta
		);
		
		if (!in_array($table, $allowed_tables, true)) {
			return array();
		}
		
		// Determine table type and use appropriate method
		$meta_keys = array();
		
		if ($table === $wpdb->postmeta) {
			// Use static query for postmeta
			$sql_query = str_replace('wp_', $wpdb->prefix, "SELECT DISTINCT(meta_key) FROM wp_postmeta ORDER BY meta_key");
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$meta_keys = $wpdb->get_col($sql_query);
		} elseif ($table === $wpdb->commentmeta) {
			// Use static query for commentmeta
			$sql_query = str_replace('wp_', $wpdb->prefix, "SELECT DISTINCT(meta_key) FROM wp_commentmeta ORDER BY meta_key");
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$meta_keys = $wpdb->get_col($sql_query);
		} elseif ($table === $wpdb->usermeta) {
			// Use static query for usermeta
			$sql_query = str_replace('wp_', $wpdb->prefix, "SELECT DISTINCT(meta_key) FROM wp_usermeta ORDER BY meta_key");
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$meta_keys = $wpdb->get_col($sql_query);
		} elseif ($table === $wpdb->termmeta) {
			// Use static query for termmeta
			$sql_query = str_replace('wp_', $wpdb->prefix, "SELECT DISTINCT(meta_key) FROM wp_termmeta ORDER BY meta_key");
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$meta_keys = $wpdb->get_col($sql_query);
		}
		
		// Cache the result for 1 hour
		wp_cache_set($cache_key, $meta_keys, '', HOUR_IN_SECONDS);
		
		return $meta_keys;
	}

}