<?php
/**
* The biobanking module
*
* PHP Version 5
*
* @category Main
* @package  Biobanking
* @author   Loris Team <loris.mni@bic.mni.mcgill.ca>
* @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
* @link     https://www.github.com/aces/Loris/
*/

/**
* Implements the candidate_list menu
*
* @category Main
* @package  Biobanking
* @author   Loris Team <loris.mni@bic.mni.mcgill.ca>
* @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
* @link     https://www.github.com/aces/Loris/
*/
class NDB_Menu_Filter_Biobanking extends NDB_Menu_Filter
{
    /**
     * Overloading this method to allow access to site users (their own site only)
     * and users w/ multisite privs
     * @return true if user has access, false otherwise
     */
    function _hasAccess()
    {
        // TODO: Create biobanking-specific permissions
        $user =& User::singleton();
        $site =& Site::singleton($user->getData('CenterID'));
        $this->tpl_data['biobanking']
            = $user->hasPermission('data_integrity_flag'); //TODO: make biobanking-specific permissions
        return (
            $user->hasPermission('data_integrity_flag')
            || ($site->isStudySite() && $user->hasPermission('data_entry'))
        );
    }

    /**
     * Sets up all the class variables needed for the candidate_list menu
     * filter
     *
     * @return true on success
     */
    function _setupVariables()
    {
        $user   =& User::singleton();
        $config =& NDB_Config::singleton();

        $this->tpl_data['toggled_visible'] =false;

        // set the class variables
        $useProjects = $config->getSetting("useProjects");
        if ($useProjects === "false") {
            $useProjects = false;
        }

       // set the class variables
       $this->columns = array(
                          'psc.Name AS Site',
                          'c.CandID AS DCCID',
                          'c.PSCID',
                          'c.Gender',
                          'bs.biospecimen_id',
			  'bs.sample_type',
			  'bs.diagnosis',
			  'bs.volume_mls',
			  'bs.sample_quality',
			  'bs.num_of_vials_available'
	       );
       if ($useProjects) {
            $this->columns[] = 'min(c.ProjectID) as Project';
        }
       # $this->columns =array_merge(
       #     $this->columns,
       #     array(
       #      'DATE_FORMAT(c.DoB,\'%Y-%m-%d\') AS DoB',
       #     )
       # );

        $this->formToFilter = array(
                               'centerID'     => 'c.CenterID',
                               'DCCID'        => 'c.CandID',
			       'PSCID'        => 'c.PSCID',
                               'gender'         => 'c.Gender',
			       'biospecimen_id' => 'bs.biospecimen_id',
			       'sample_type' => 'bs.sample_type',
			       'sample_quality' => 'bs.sample_quality',
			       'diagnosis' => 'bs.diagnosis'
		);

        $this->validFilters = array(
                               'c.CenterID',
                               'c.CandID',
                               'c.PSCID',
			       'c.Gender',
			       'bs.biospecimen_id',
			       'bs.sample_type',
			       'bs.sample_quality',
			       'bs.sample_diagnosis',
			       'bs.date_collected'
		);

        $this->query = " FROM candidate c
            LEFT JOIN psc ON (c.CenterID=psc.CenterID)
            LEFT JOIN biospecimen bs ON (bs.CandID=c.CandID)
            WHERE c.Entity_type = 'Human' AND c.Active = 'Y'";

        if (!$user->hasPermission('data_integrity_flag')) {
            $this->query .= " AND c.CenterID=" . $user->getCenterID();
        }

        $this->group_by = '';#'c.CandID, psc.Name, c.PSCID, c.Gender';
        $this->order_by = 'psc.Name, c.CandID DESC';
#
#        if ($useProjects) {
#            $this->validFilters[] = 'c.ProjectID';
#        }
#        $this->validFilters = array_merge(
#            $this->validFilters,
#            array(
#             'c.DoB',
#            )
#        );
#
        $this->EqualityFilters    = array(
                                     'c.CenterID',
                                     'c.Gender',
                                     'bs.sample_type'
                                    );

#        if ($useProjects) {
#            $this->formToFilter = array_merge(
#                $this->formToFilter,
#                array('ProjectID' => 'c.ProjectID')
#            );
#        }
#        $this->formToFilter = array_merge(
#            $this->formToFilter,
#            array(
#             'dob'                 => 'c.DoB',
#            )
#        );

        return true;
    }


    /**
     * Create the form for the biobanking menu page
     *
     * @return none
     */
    function _setFilterForm()
    {
        // create user object
        $user   =& User::singleton();
        $config =& NDB_Config::singleton();

        // PSC
        if ($user->hasPermission('data_integrity_flag')) {
            // get the list of study sites - to be replaced by the Site object
            $list_of_sites = Utility::getSiteList();
            if (is_array($list_of_sites)) {
                $list_of_sites = array('' => 'All') + $list_of_sites;
            }
        } else {
            // allow only to view own site data
            $site =& Site::singleton($user->getData('CenterID'));
            if ($site->isStudySite()) {
                $list_of_sites = array();
                $list_of_sites[$user->getData('CenterID')]
                    = $user->getData('Site');
            }
        }

        // SubprojectID
        $list_of_subprojects = Utility::getSubprojectList();

        $sample_options = array(
                         null => 'All',
                         'DNA'  => 'DNA',
                         'PBMC'  => 'PBMC',
                         'Plasma'  => 'Plasma',
                         'Serum'  => 'Serum'
                        );

        // add form elements
        $this->addSelect('centerID', 'Site', $list_of_sites);
        $this->addBasicText('PSCID', 'PSCID');
        $this->addBasicText('DCCID', 'DCCID');
        $this->addSelect('sample_type', 'Sample Type', $sample_options);
        $this->addBasicText('sample_id', 'Sample ID');
        $this->addBasicText('sample_quality', 'Sample Quality');
        $this->addBasicText('biospecimen_id', 'Biospecimen ID', array('maxlength' => 10));
        $this->addBasicText('diagnosis', 'Diagnosis', array('maxlength' => 10));

        // Project list, if applicable
        if ($config->getSetting("useProjects")==="true") {
            $list_of_projects = array(null => 'All');
            $projectList      = Utility::getProjectList();
            foreach ($projectList as $key => $value) {
                $list_of_projects[$key] =$value;
            }
            $this->addSelect('ProjectID', 'Project', $list_of_projects);
        }
        if ($config->getSetting("useEDC")=="true") {
            $this->addBasicText(
                'edc',
                'EDC',
                array('maxlength' => 10)
            );
            $this->tpl_data['useEDC'] = "true";
        }

        return true;
    }


    /**
     * Overrides the default setDataTableRows to assign the background colour
     * to the feedback thread column
     *
     * @param integer $count The number of rows in the table
     *
     * @return true on success
     */
    function _setDataTableRows($count)
    {
        // create user object
        $user =& User::singleton();

        $subprojectlist = Utility::getSubprojectList();
        $projectlist    = Utility::getProjectList();

        // print out
        $x = 0;
        foreach ($this->list as $item) {
            //count column
            $this->tpl_data['items'][$x][0]['value'] = $x + $count;

            //print out data rows
            $i = 1;
            foreach ($item as $key => $val) {
                $itemi =& $this->tpl_data['items'][$x][$i];
                // if it's a non-PSC user show the URL to the timepoint list on
                // PSCID field
                if ($key == 'PSCID' && $user->hasPermission('data_integrity_flag')) {
                    $itemi['DCCID'] = $item['DCCID'];
                }
                $itemi['PSCID'] = $item['PSCID'];
                $itemi['name']  = $key;
                $itemi['value'] = $val;
                // get feedback information for each displayed candidate record
                $i++;
	    }

            $x++;
        }
        $this->tpl_data['numCandidates'] = $this->TotalItems;
        return true;
    }
}
?>
