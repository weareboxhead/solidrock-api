<?php
/**
 * SolidrockApi plugin for Craft CMS 3.x
 *
 * Communicate and process data from the Solidrock API
 *
 * @link      https://boxhead.io
 * @copyright Copyright (c) 2018 Boxhead
 */

namespace boxhead\solidrockapi\services;

use boxhead\solidrockapi\SolidrockApi;

use Craft;
use craft\base\Component;

use GuzzleHttp\Client;


/**
 * Churches Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Boxhead
 * @package   SolidrockApi
 * @since     1.0.0
 */
class Api extends Component
{
    private $settings;
    private $client;

    // Public Methods
    // =========================================================================

    /**
     * Performs a request on the Solidrock API
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $enableCache
     * @param int $cacheExpire
     * @return array|null
     */

    public function call($method = 'POST', $uri, $params = array(), $headers = array(), $enableCache = false, $cacheExpire = 0)
    {
	    $this->settings = SolidrockApi::$plugin->getSettings();

        // Check for all required settings
        $this->checkSettings();

        // Create Guzzle Client
        $this->createGuzzleClient();

        // Get results from cache
        // if ($enableCache)
        // {
        //     $key = 'solidrock.' . md5($uri . serialize($params));

        //     $response = craft()->fileCache->get($key);

        //     if ($response)
        //     {
        //         return $response;
        //     }
        // }

        // No cache, or disabled so run request
        try
        {
            
            $uri = $uri . '.json';
            
            $params['apiKey'] = $this->settings->apiKey;

            $request = $this->client->request($method, $uri, [
                'form_params' => $params
            ]);
                
            // Cache the response
            // if ($enableCache)
            // {
            //     craft()->fileCache->set($key, $response, $cacheExpire);
            // }

            // Do we have a success response?
            if ($request->getStatusCode() !== 200) {

                Craft::error('SolidrockApi: API Reponse Error ' . $request->getStatusCode() . ": " . $request->getReasonPhrase(), __METHOD__);

                return false;
            }

            $body = json_decode($request->getBody());
            // $body = $request->getBody();

            // Return results
            return $body;
        }
        catch(\Guzzle\Http\Exception\ClientErrorResponseException $e)
        {
            Craft::error("SolidrockApi: Couldn't get Solidrock response", __METHOD__);
        }
        catch(\Guzzle\Http\Exception\CurlException $e)
        {
            Craft::error("SolidrockApi: ".$e->getMessage(), __METHOD__);
        }
    }



    // Private Methods
    // =========================================================================

    private function dd($data)
    {
        echo '<pre>'; print_r($data); echo '</pre>';
        die();
    }
    

    private function createGuzzleClient()
    {
        $this->client = new Client([
            'base_uri' => $this->settings->apiUrl,
            'auth' => [
                $this->settings->apiUsername,
                $this->settings->apiPassword
            ],
            'verify' => false, // @TODO not sure why this is needed locally 
            'form_params' => [
                'apiKey' => $this->settings->apiKey
            ]
        ]);
    }

    private function checkSettings()
    {
        if (!$this->settings->apiUrl) {
            Craft::error('SolidrockApi: No API URL provided in settings', __METHOD__);

            return false;
        }
        
        if ($this->settings->apiKey === null) {
            Craft::error('SolidrockApi: No API Key provided in settings', __METHOD__);

            return false;
        }

        if ($this->settings->apiUsername === null) {
            Craft::error('SolidrockApi: No Solidrock Username provided in settings', __METHOD__);

            return false;
        }

        if ($this->settings->apiPassword === null) {
            Craft::error('SolidrockApi: No Solidrock Password provided in settings', __METHOD__);

            return false;
        }
    }
}
