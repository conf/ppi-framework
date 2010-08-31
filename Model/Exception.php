<?php


/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Digiflex Development
 * @package   PPI
 * @subpackage core
 */
class PPI_Model_Exception extends PPI_Model 
{
	
	public function __construct ()
	{
		parent::__construct('ppi_errors', 'id');
	}
	
	public function deleteRecord($p_iRecordID="")
	{
		if (empty ($p_iRecordID))
		 return false;
		 
		$sQuery = "
		 DELETE FROM
		  ppi_errors
		 WHERE
		  id='".mysql_real_escape_string($p_iRecordID)."'";
	  return  $this->query ($sQuery, __FUNCTION__);
	}
}