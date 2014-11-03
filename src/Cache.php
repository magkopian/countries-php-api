<?php namespace JeroenDesloovere\Countries;

/**
 * Cache
 *
 * @author Manolis Agkopian <m.agkopian@gmail.com>
 */
class Cache
{
    private $dir;
    private $id = '26b39d1efb75ff1b36ed05a714bf156d';
    private $expirationTime = 86400; // 24 hours
    
    /**
     * __construct
     *
     * @param string $dir
     * @param string[optional] $id
     * @throws CountriesException
     */
    public function __construct($dir, $expirationTime = null, $id = null)
    {
        if ($expirationTime !== null) {
            $this->expirationTime = $expirationTime;
        }
        
        if ($id !== null) {
            $this->id = $id;
        }
        
        $this->dir = $dir;
        
        // add a trailing slash if needed
        if (substr($this->dir, -1) != '/') {
            $this->dir .= '/';
        }
        
        // if directory does not exists create it
        if (!file_exists($this->dir)) {
            if (is_writable(dirname($this->dir)) === false) {
                throw new CountriesException('Unable create cache directory, no write access to the parent directory');
            }
            else {
                if (mkdir($this->dir, 0755) === false) {
                    throw new CountriesException('Unable create cache directory');
                }
            }
        }
    }
    
    /**
     * get
     *
     * @param  array[optional] $params
     * @return string
     */
    public function get($params = array())
    {
        if (!file_exists($this->getFilename($params))) {
            return null;
        }
        
        $response = file_get_contents($this->getFilename($params));
        if ( $response === false || empty($response) ) {
            return null;
        }
        
        $items = json_decode($response, true);
        if ($items['expires'] < time()) {
            return null;
        }
        
        return $response;
    }
    
    /**
     * set
     *
     * @param  string $response
     * @param  array[optional] $params
     * @throws CountriesException
     */
    public function set($response, $params = array())
    {
        $items = json_decode($response, true);
        $items['expires'] = time() + $this->expirationTime;
        $response = json_encode($items);
        
        if (file_put_contents($this->getFilename($params), $response) === false) {
           throw new CountriesException('Unable to write to cache');
        }
    }
    
    /**
     * getFilename
     *
     * @param  array[optional] $params
     * @return string
     */
    private function getFilename($params = array())
    {
        // init default
        if (count($params) == 0) $params = array('lang' => 'nl');
        
        // make sure the parameters are all in correct order
        ksort($params);
        
        return $this->dir . $this->id . '_' . implode('_', $params);
    }
}

