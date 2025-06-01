<?php defined( 'ABSPATH' ) or die( 'No direct access allowed' );

add_action('rest_api_init', function () {

	$mainObjectTypes = ['post', 'comment', 'user', 'term'];
	$metaPostTypes = getMetafieldsWithPostTypes();

	foreach ($mainObjectTypes as $mainObjectType) {
		$mainObjectTypeMetaItems = get_option('zinn_zwram_options_' . $mainObjectType);

		if (!empty($mainObjectTypeMetaItems)) {
			foreach ($mainObjectTypeMetaItems as $metaItemFull => $val) {

				$metaItem = str_replace(ZINN_ZWRAM_FIELD_PREFIX, '', $metaItemFull);

				if ($mainObjectType == 'post') {
					if (isset($metaPostTypes[$metaItem])) {
						foreach ($metaPostTypes[$metaItem] as $postTypeOfTheMetafield) {
							register_rest_field(
								$postTypeOfTheMetafield,
								$metaItem,
								[
									'get_callback' => function ($object, $fieldName, $request) use ($mainObjectType) {
										return get_metadata($mainObjectType, $object['id'], $fieldName, true);
									}
								]
							);
						}
					}
				} else {
					register_rest_field(
						$mainObjectType,
						$metaItem,
						[
							'get_callback' => function ($object, $fieldName, $request) use ($mainObjectType) {
								return get_metadata($mainObjectType, $object['id'], $fieldName, true);
							}
						]
					);
				}


			}
		}
	}
});


/**
 * Gets all metafields with array of post types it is related to.
 * @return array
 */
function getMetafieldsWithPostTypes()
{
	global $wpdb;
	
	// Try to get from cache first
	$cache_key = 'zinn_zwram_meta_post_types';
	$cached_result = wp_cache_get($cache_key);
	
	if ($cached_result !== false) {
		return $cached_result;
	}
	
	// Use a static query string to avoid interpolation issues
	$sql_query = "
		SELECT DISTINCT
			p.post_type,
			m.meta_key
		FROM wp_postmeta m
		INNER JOIN wp_posts p ON p.ID = m.post_id
		ORDER BY
			post_type,
			meta_key
	";
	
	// Replace wp_ with actual prefix
	$sql_query = str_replace('wp_', $wpdb->prefix, $sql_query);
	
	// Execute query - we can't use prepare() here because there are no user inputs
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	$metaItems = $wpdb->get_results($sql_query);

	$out = [];
	if (!empty($metaItems)) {
		foreach ($metaItems as $metaItem) {
			if (!isset($out[$metaItem->meta_key]) || !in_array($metaItem->post_type, $out[$metaItem->meta_key])) {
				$out[$metaItem->meta_key][] = $metaItem->post_type;
			}
	    }
	}
	
	// Cache the result for 1 hour
	wp_cache_set($cache_key, $out, '', HOUR_IN_SECONDS);
	
	return $out;
}