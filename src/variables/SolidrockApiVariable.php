<?php
/**
 * @link      http://boxhead.io
 * @copyright Copyright (c) 2015, Boxhead
 * @license   http://boxhead.io
 */

namespace boxhead\solidrockapi\variables;

use boxhead\solidrockapi\SolidrockApi;

use Craft;

class SolidrockApiVariable
{
    // Public Methods
    // =========================================================================

	public function api($method, $uri, $params = array(), $headers = array(), $enableCache = false, $cacheExpire = 0)
	{
        try
        {
            return SolidrockApi::$plugin->api->call($method, $uri, $params, $headers, $enableCache, $cacheExpire);
        }
        catch(\Exception $e)
        {
            return false;
        }
	}
}