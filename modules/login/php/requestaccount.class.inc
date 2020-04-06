<?php
/**
 * Implements the LORIS request account page.
 *
 * PHP version 5
 *
 * @category   Behavioural
 * @package    Main
 * @subpackage Imaging
 * @author     Dave MacFarlane <david.macfarlane2@mcgill.ca>
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link       https://www.github.com/aces/Loris/
 */
namespace LORIS\login;

/**
 * Implements the LORIS request account page.
 *
 * @category   Behavioural
 * @package    Main
 * @subpackage Imaging
 * @author     Dave MacFarlane <david.macfarlane2@mcgill.ca>
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link       https://www.github.com/aces/Loris/
 */
class RequestAccount extends \NDB_Form
{
    /**
     * Sets up the form elements on the page.
     *
     * @return void
     */
    function setup()
    {
	    // Serve the files as downloadable links upon GET. If POST present, do not
	    // serve files as this may interfere with file downloading.
	    // This is a hack...
	    if (isset($_GET['download']) && empty($_POST)) {
		    switch ($_GET['download']) {
			    case 'mta':
				    //TODO Change this to link to the MTA once it has been
				    //de-identified.
				    //$filename = 'mta.doc';
				    $filename = 'mcgill-user-consent-form.docx';
				    break;
			    case 'consent':
				    $filename = 'mcgill-user-consent-form.docx';
				    break;
			    default:
				    http_response_code(400);
				    return;
		    }
		    header('Content-Disposition: attachment; filename=' . $filename);
		    $fp = fopen("/data/loris/account_requests/$filename", 'r');
		    fpassthru($fp);
		    fclose($fp);
	    }
        $config   = \NDB_Config::singleton();
        $tpl_data = array();
        $this->tpl_data['page_title'] = 'Request Account';

        $this->addBasicText(
            "firstname",
            "First name",
            array(
                "placeholder" => "First Name",
                "required"    => true,
            )
        );
        $this->addBasicText(
            "lastname",
            "Last name",
            array(
                "placeholder" => "Last Name",
                "required"    => true,
            )
        );
        $this->addBasicText(
            "from",
            "Email address",
            array(
                "placeholder" => "Email address",
                "required"    => true,
            )
        );

        //merging array using + sign to avoid reindexing of keys which
        //occurs when array_merge() is used
        $this->addSelect(
            "site",
            "Site",
            array("" => "Choose your site:") + \Utility::getSiteList(),
            array("required" => true)
        );
        $this->addCheckbox("examiner", "Examiner role", array());
        $this->addCheckbox("radiologist", "Radiologist", array());

	// Add consent elements.
	$tpl_data['mta'] = 'test';
	$tpl_data['mcgillconsent'] = 'test';
	$this->addCheckbox(
		"consent", 
		"I confirm that I have read and understood the MTA & Approved Application"
		. " and agree to act in accordance with all terms and conditions therein."
		,
		array('required' => true)
	);

	$this->addFile(
		'upload', 
		'Supporting Document', 
		array(
			"placeholder" => "Upload file...",
			"required"    => true,
		)
	);

        // Get reCATPCHA keys
        $reCAPTCHAPrivate = $config->getSetting('reCAPTCHAPrivate');
        $reCAPTCHAPublic  = $config->getSetting('reCAPTCHAPublic');
        // Display reCAPTCHA if both private and public keys are set
        if ($reCAPTCHAPrivate && $reCAPTCHAPublic) {
            $tpl_data['captcha_key'] = $reCAPTCHAPublic;
        }

        $this->form->addFormRule(array(&$this, '_validate'));
    }

    /**
     * _validate validates the data that was submitted by the user
     *
     * @param array $values The values that were submitted to the page.
     *
     * @return array a (possibly empty) array of errors
     */
    function _validate($values): array
    {

        $errors = array();
        // FIXME: These error checks are direct ports from when the request
        // account page was a standalone php script (with the ridiculous
        // "name must be at least 3 characters" check removed, because some
        // people don't have three character long names..) The "required" checks
        // should probably be replaced with a required rule.
        if (empty($values['firstname'])) {
            $errors['firstname'] = "First name is required!";
        }
        if (empty($values['lastname'])) {
            $errors['lastname'] = "Last name is required!";
        }
        if (empty($values['from'])) {
            $errors['from'] = "Please provide a valid email!";
        } else if (!filter_var($_REQUEST['from'], FILTER_VALIDATE_EMAIL) ) {
            $errors['from'] = 'Please provide a valid email!';
        }

        if (empty($values['site'])) {
            $errors['site'] = "Please choose a site!";
        }

	if (!isset($values['consent'])) {
	    $errors['consent'] = "You must consent to proceed.";
	}

	#if (empty($_FILES)) {
	#	error_log('_FILES is empty');
	#    $errors['upload'] = "You must upload a file.";
	#}

        // I don't think this is required because insert uses prepared statements,
        // but I'm keeping it for now because it doesn't do any harm (unlike the
        // len > 3 check). Little Bobby Tables will just have to sign
        // up using a table alias. - Dave
        //
        // For each fields, check if quotes or if some HTML/PHP
        // tags have been entered
        foreach ($values as $field => $value) {
            if (preg_match('/["]/', html_entity_decode($value))) {
                $errors[$field] = "You can't use quotes in $field";
            }
            if (strlen($value) > strlen(strip_tags($value))) {
                $errors[$field] = "You can't use tags in $field";
            }
        }

        return $errors;
    }

    /**
     * _process inserts the new account with a pending flag after
     * the input has been validated.
     *
     * @param array $values The values that were submitted to the page.
     *
     * @return void
     */
    function _process($values)
    {

        $DB       = \Database::singleton();
        $name     = htmlspecialchars($_REQUEST["firstname"], ENT_QUOTES);
        $lastname = htmlspecialchars($_REQUEST["lastname"], ENT_QUOTES);
        $from     = htmlspecialchars($_REQUEST["from"], ENT_QUOTES);
        $site     = $_REQUEST["site"];
        $fullname = $name." ".$lastname;

        $vals = array(
            'UserID'           => $from,
            'Real_name'        => $fullname,
            'First_name'       => $name,
            'Last_name'        => $lastname,
            'Pending_approval' => 'Y',
            'Email'            => $from,
        );

        // check email address' uniqueness
        $result = $DB->pselectOne(
            "SELECT COUNT(*) FROM users WHERE Email=:VEmail OR UserID=:VEmail",
            array('VEmail' => $from)
        );

        $user_id = null;

        if ($result == 0) {
            // insert into DB only if email address doesn't exist
            $DB->insert('users', $vals);
            // Get the ID of the new user and insert into user_psc_rel
            $user_id = $DB->getLastInsertId();

            $DB->insert(
                'user_psc_rel',
                array(
                    'UserID'   => $user_id,
                    'CenterID' => $site,
                )
            );

            $examinerCheck    = $_REQUEST['examiner'] ?? 'off';
            $radiologistCheck = $_REQUEST['radiologist'] ?? 'off';

            if ($examinerCheck == 'on') {
                $rad = 0;
                if ($radiologistCheck == 'on') {
                    $rad = 1;
                }
                //insert in DB as inactive until account approved
                $examinerID = $DB->pselect(
                    "SELECT e.examinerID
                 FROM examiners e
                 WHERE e.full_name=:fn",
                    array("fn" => $fullname)
                );

                // If examiner not in table add him
                if (empty($examinerID)) {
                    $DB->insert(
                        'examiners',
                        array(
                            'full_name'   => $fullname,
                            'radiologist' => $rad,
                            'userID'      => $user_id,
                        )
                    );
                    $examinerID = $DB->getLastInsertID();
                    $DB->insert(
                        'examiners_psc_rel',
                        array(
                            'examinerID'       => $examinerID,
                            'centerID'         => $site,
                            'active'           => 'Y',
                            'pending_approval' => 'Y',
                        )
                    );
                }
            }
        }

        // Verify reCAPTCHA
        $err = array();
        if (isset($values['g-recaptcha-response']) && isset($reCAPTCHAPrivate)) {
            $recaptcha = new \ReCaptcha\ReCaptcha($reCAPTCHAPrivate);
            $resp      = $recaptcha->verify(
                $values['g-recaptcha-response'],
                $_SERVER['REMOTE_ADDR']
            );
            if (!$resp->isSuccess()) {
                $errors         = $resp->getErrorCodes();
                $err['captcha'] = 'Please complete the reCaptcha!';
            }
        }

	// Do file upload. TODO This is provisional code used to get a demo working.
	// It should not be used on production. Update this code to use the class
	// FilesUploadHandler ASAP!
	#$filepath = 
	#	'/data/loris/account_requests/' . 
	#	sprintf("%s_%s_%s/", $lastname, $name, date('Y-m-d'));
	#error_log($filepath);
	#mkdir($filepath, 0755, true);
	#if (move_uploaded_file($_FILES["upload"]["tmp_name"], $filepath . 'consent') === false) {
	#	error_log('Could not upload file.');
	#	$err['upload'] = 'Upload failed. Please contact the administrator.';
	#}
	#chmod($filepath . 'consent', 400);

        // Show success message even if email already exists for security reasons
        $this->tpl_data['success'] = true;
    }
}

