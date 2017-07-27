<?php

namespace hypeJunction\GameMechanics;

class Media {

	/**
	 * Set the correct url for badge thumbnails
	 *
	 * @param string $hook         name of the hook
	 * @param string $type         type of the hook
	 * @param string $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return string
	 */
	function setIconFile($hook, $type, $return_value, $params) {
		
		if (empty($params) || !is_array($params)) {
			return $return_value;
		}
		
		$badge = elgg_extract("entity", $params);
		if (empty($badge) || !($badge instanceof Badge)) {
			return $return_value;
		}
		
		$size = elgg_extract("size", $params, "medium");
		$iconsizes = elgg_get_config("icon_sizes");
		if (empty($size) || empty($iconsizes) || !array_key_exists($size, $iconsizes)) {
			return $return_value;
		}
		
		$icontime = $badge->icontime;
		if (empty($icontime)) {
			return $return_value;
		}
		
		$params = array(
			"badge_guid" => $badge->getGUID(),
			"guid" => $badge->getOwnerGUID(),
			"size" => $size,
			"icontime" => $icontime
		);
		
		return elgg_http_add_url_query_elements("mod/gc_gamification/thumbnail.php", $params);
	}

}
