<?php
/**
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 * @author Paul Dragoonis <dragoonis@php.net>
 * @since Version 1.0
 */
class PPI_Config_Ini extends PPI_Config
{
    /**
     * String that separates nesting levels of configuration data
     *
     * @var string
     */
    protected $_nestChar = '.';

    /**
     * Loads the section $section from the config file for
     * access facilitated by nested object properties.
     *
     * @param  string        $filename
     * @param  string|null   $section
     * @throws PPI_Exception
     */
    public function __construct($iniArray, $section) {
     	if (!isset($iniArray[$section])) {
     		throw new PPI_Exception('Unable to find section ' . $section . ' in ini config');
     	}

    	// Lets actually setup and parse the config into respective arrays
		$config = array();
    	foreach ($iniArray[$section] as $key => $value) {
    		$config = $this->_processKey($config, $key, $value);
    	}
    	// Set the data into PPI_Config so it can iterate and
		parent::__construct($config);
        $this->_loadedSection = $section;
    }

    /**
     * Assign the key's value to the property list. Handle the "dot"
     * notation for sub-properties by passing control to
     * processLevelsInKey().
     *
     * @param  array  $config
     * @param  string $key
     * @param  string $value
     * @throws PPI_Exception
     * @return array
     */
    protected function _processKey($config, $key, $value)
    {
        if (strpos($key, $this->_nestChar) !== false) {
            $pieces = explode($this->_nestChar, $key, 2);
        	$firstPiece = $pieces[0];
        	if($firstPiece != '' && $pieces[1] != '') {
                if (!isset($config[$firstPiece])) {
                    $config[$firstPiece] = array();

                } elseif (!is_array($config[$firstPiece])) {
                    /**
                     * @see PPI_Exception
                     */
                    die("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                if(is_array($value)) {
                	// Bring all the array elements together and see if they contain a ::
                	// With the :: set we explode them to set a key=>val relationshiip
                	if(strpos(implode('-', array_values($value)), '::') !== false) {
	                	$newValues = array();
	                	foreach($value as $arrayVal) {
	                		list($newKey, $newVal) = explode('::', $arrayVal, 2);
	                		$newValues[$newKey] = $newVal;
	                	}
	                	$value = $newValues;
                		unset($newValues);
                	}
                }
                $config[$firstPiece] = $this->_processKey($config[$firstPiece], $pieces[1], $value);
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }
}