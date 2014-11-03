<?php namespace JeroenDesloovere\Countries;

/**
 * Countries
 *
 * Get all the countries in the world and their languages.
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 * @author Manolis Agkopian <m.agkopian@gmail.com>
 */
class Countries
{
    /**
     * API URL
     */
    const API_URL = 'http://www.geonames.org/countryInfoJSON';

    /**
     * cache
     */
    private $cache;    
    
    /**
     * __construct
     *
     * @param Cache[optional] $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Do call
     *
     * @param  array[optional] $params
     * @return array
     */
    protected function doCall($params = array())
    {
        // init results
        $results = array();

        // init default
        if (count($params) == 0) $params = array('lang' => 'nl');

        // check cache if available
        $response = null;
        if ($this->cache !== null) {
            $response = $this->cache->get($params);
        }
        
        if ($response === null) {

            // init curl
            $curl = curl_init();

            // set options
            curl_setopt($curl, CURLOPT_URL, self::API_URL . '?' . http_build_query($params));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            
            // execute
            $response = curl_exec($curl);

            // close
            curl_close($curl);
            
            if ($this->cache !== null) {
                $this->cache->set($response, $params);
            }
        
        }
        
        // define items
        $items = json_decode($response, true);

        // loop items
        foreach ($items['geonames'] as $item) {
            // add to results
            $results[$item['countryCode']] = $item;
        }

        // return results
        return $results;
    }

    /**
     * Get all countries
     *
     * @param  string[optional] $language
     * @return countries        Returns countryCode => countryName
     */
    public function getAll($language = null)
    {
        // init results
        $results = array();

        // define parameters
        $parameters = (empty($language)) ? array() : array('lang' => (string) $language);

        // get items
        $items = $this->doCall($parameters);

        // loop items
        foreach ($items as $countryCode => $item) {
            // add to results
            $results[$countryCode] = $item['countryName'];
        }

        // return
        return $results;
    }

    /**
     * Get languages for a country
     *
     * @param  string $countryCode The country code where you want to get the languages for.
     * @return array
     */
    public function getLanguages($countryCode = null)
    {
        // redefine countryCode
        $countryCode = (string) $countryCode;

        // get items
        $items = $this->doCall();

        // error checking
        if (!isset($items[$countryCode])) {
            throw new CountriesException('Country with code: "' . $countryCode . '" doesn\'t exists');
        }

        // return languages
        return explode(',', $items[$countryCode]['languages']);
    }
}

