<?php
/**
 * Countries test
 *
 * Get all the countries in the world and their languages.
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */

require_once __DIR__ . '/../vendor/autoload.php';

use JeroenDesloovere\Countries;

// instantiate a Countries object
$countries = new Countries\Countries(new Countries\Cache('cache'));

// get items
$items = $countries->getAll();

// dump items
print_r($items);

// get languages for country
$items = $countries->getLanguages('BE');

// dump items
print_r($items);

