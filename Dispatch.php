<?php

/**
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 * @author Paul Dragoonis <dragoonis@php.net>
 * @since Version 1.0
 */


class PPI_Dispatch extends PPI_Input {
	private $_controllerDirectory 	= '';
	private $_controllerName 		= '';
	private $_controllerFileName 	= '';
	private $_masterController 		= '';
	private $_controllerUrl			= '';
	private $_methodName 			= '';
	private $_pluginController		= false;
	private $_controllerInstance;
    private static $_instance = null;

    /**
     * Create the instance
     * @return void
     */
    protected static function init() {
        self::setInstance(new PPI_Dispatch());
    }

    /**
     * Set the current instance of PPI_Dispatch
     *
     * @param PPI_Dispatch $instance The instance object
     */
    public static function setInstance(PPI_Dispatch $instance) {
        if (self::$_instance !== null) {
            throw new PPI_Exception('Dispatcher is already initialised');
        }
        self::$_instance = $instance;
    }

    /**
     * Obtains the current set instance if it doesn't exist then it will make it
     * @return PPI_Dispatch
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::init();
        }
        return self::$_instance;
    }

    /**
     * Identify and store the appropriate Controller and Methods to dispatch at a later time when calling dispatch()
     *
     */
	function __construct() {
		parent::__construct();
		$oConfig    = PPI_Helper::getConfig();
		// Build up a full URL.
		$sProtocol  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		$sFullUrl   = $sProtocol . '://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];
        $sBaseUrl   = $oConfig->system->base_url;
        // Identify if the baseurl is not contained within the full current url, then it's misconfigured.
        if(stripos($sFullUrl, $sBaseUrl) === false) {
    		PPI_Exception::show_404();
        }

		// Subtract the BaseUrl from the actual full URL and then what we have left is our controllers..etc
		$aUrls            = explode('/', trim(str_replace($sBaseUrl, '', $sFullUrl), '/'));
		$sControllerName  = (count($aUrls) > 0) ? $aUrls[0] : '';

		// See if the mastercontroller exists in the config
		if(!isset($oConfig->system->masterController)) {
			throw new PPI_Exception('Unable to find mastercontroller in general.ini configuration file');
		}
		$sMasterController   = $oConfig->system->masterController;
		// If the mastercontroller is needed.
		$sControllerName     = ($sControllerName == '') ? $sMasterController : $sControllerName;
		$sControllerFileName = ucfirst((($sControllerName == '') ? $sMasterController : $sControllerName));
		$this->setControllerName($sControllerName);
		$this->setControllerFileName("APP_Controller_$sControllerFileName"); // eg: APP_Controller_User
		return $this; // Fluent Interface
	}

	/**
	 * Actually dispatch the relevant controller identified by the __construct()
	 *
	 * @return $this Fluent Interface
	 */
	function dispatch() {

		$oConfig  = PPI_Helper::getConfig();
		$sFileName = $this->getControllerFileName();
		$this->_controllerUrl = $sFileName;		
		if(class_exists($sFileName)) {
			$oController = new $sFileName();
			$sMethod     = parent::get($this->getControllerName());
			$aMethods    = get_class_methods(get_class($oController));
			
			// Did we specify a method ?
			if($sMethod != '') {
				// Does our method exist on the class
				if(!in_array($sMethod, $aMethods)) {
					PPI_Exception::show_404();
				}
			} else {
				$sMethod = 'index';
			}
			$this->setMethodName($sMethod);
			
			// Try and remember why we have usePPI, if we don't need it anymore then remove it.
			if(isset($oConfig->system->acl->enabled) 
					&& $oConfig->system->acl->enabled == true 
					&& $oConfig->system->acl->usePPI == true) {
					
				$oController->checkAuth();
			}
			// Call up the relevant controller and method
			$oController->$sMethod();
			return $this; // Method chain
		}
		PPI_Exception::show_404();
	}
	function setControllerInstance($p_oController) {
		$this->_controllerInstance = $p_oController;
	}
	function getControllerInstance() {
		return $this->_controllerInstance;
	}
	function getUrlController() {
		return $this->_controllerUrl;
	}
	function setMethodName($p_sMethodName) {
		$this->_methodName = $p_sMethodName;
	}
	function getMethodName() {
		return $this->_methodName;
	}
	function getControllerName() {
		return $this->_controllerName;
	}
	function setControllerName($p_sControllerName) {
		$this->_controllerName = $p_sControllerName;
	}
	function isPluginController() {
		return $this->_pluginController;
	}
	function getControllerFileName() {
		return $this->_controllerFileName;
	}
	function setControllerFileName($p_sUrlController) {
		$this->_controllerFileName = $p_sUrlController;
	}
}
