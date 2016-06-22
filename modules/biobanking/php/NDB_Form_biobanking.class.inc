<?php
/**
 * Form_biobanking class file
 *
 * PHP version 5
 *
 * @category   Main
 * @package    Biobanking
 * @author     Loris Team <loris.mni@bic.mni.mcgill.ca>
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link       https://github.com/aces/Loris
 */
require_once 'NDB_Form.class.inc';
/**
 * NDB_Form_Genomic_Browser Class
 *
 * This class is genomic_browser Form
 *
 * @category   Biobank
 * @package    Main
 * @subpackage Biobank
 * @author     John Saigle <jsaigle.mcin@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link       https://github.com/aces/Loris
 */
class NDB_Form_biobanking extends NDB_Form
{
    var $candID;
    var $sessionID;
    /**
     * Determine whether the user has permission to view this page
     *
     * @return bool whether the user has access
     */

    function _hasAccess()
    {
        // TODO: Create biobanking-specific permissions
        $user =& User::singleton();
        $site =& Site::singleton($user->getData('CenterID'));
        $this->tpl_data['biobanking']
            = $user->hasPermission('data_integrity_flag');
        return (
            $user->hasPermission('data_integrity_flag')
            || ($site->isStudySite() && $user->hasPermission('data_entry'))
        );
    }
    /**
     * Sets up main parameters given one GenomicFileID
     * Visit Label or SessionID not required
     *
     * @return NULL
     */
    function viewBiospecimenInfo()
    {
	$this->DB = Database::singleton();
	$this->biospecimen_id = $_REQUEST['biospecimen_id'];
	// add check? if (!empty($this->genomic_file_ID) &&
	// is_numeric($this->genomic_file_ID)) {
	if (!empty($this->biospecimen_id) ) {
	    $this->_setSpecimenData($this->biospecimen_id);
	}
	$this->tpl_data['has_permission'] = true;#($this->_hasAccess()) ? true : false;
    }
    /**
     * Sets values for Genomic data files
     *
     * @param string $category type of data: raw,cleaned,gwas
     *
     * @return NULL
     */
    function _setSpecimenData($biospecimen_id)
    {
	$info = $this->DB->pselect(
		"SELECT *
		FROM biospecimen WHERE biospecimen_id=:bid", 
		$params = array('bid' => $this->biospecimen_id)
		);
	$this->tpl_data['info'] = $info;
    }
}
?>
