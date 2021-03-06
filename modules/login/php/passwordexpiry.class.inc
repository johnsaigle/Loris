<?php declare(strict_types=1);
/**
 * Implements the user password expiry page in LORIS, to reset
 * expired passwords.
 *
 * PHP version 7
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
 * Implements the user password expiry page for LORIS.
 *
 * @category   Behavioural
 * @package    Main
 * @subpackage Imaging
 * @author     Dave MacFarlane <david.macfarlane2@mcgill.ca>
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link       https://www.github.com/aces/Loris/
 */
class PasswordExpiry extends \NDB_Form
{
    /**
     * The username that we're displaying the password expired page for.
     * This is used for validation.
     */
    private $_username;

    /**
     * The user object. This is not populated until after the validation
     * is done, but is stored locally so we don't need to get a new copy of
     * it in _process to call updatePassword.
     */
    private $_user;

    /**
     * Setup sets up the LorisForm elements for the page, and redirects
     * to the main LORIS page if the PasswordExpiredForUser session variable
     * hasn't been set in SinglePointLogin (which means it shouldn't be on
     * the password expiry page.)
     *
     * @return void
     */
    function setup()
    {
        if (empty($_SESSION['PasswordExpiredForUser'])) {
            // HACK ALERT: If the server didn't set the variable in the PHP session
            // in SinglePointLogin, we shouldn't be here, so redirect to the main
            // page and cry.
            header("HTTP/1.1 303 See Other");
            header("Location: /");
            // HACK ALERT #2: hard exiting is always the wrong thing to do.
            exit(0);
        }
        $this->_username = $_SESSION['PasswordExpiredForUser'];
        $this->addBasicText("password", "");

        // HACK ALERT #3: The old password expiry page required the fields
        // username/password to be present so that SinglePointLogin would
        // properly be authenticated after changing the password, otherwise
        // it'll go to the login page instead of logging in after updating
        // the password.
        //
        // (This is also why the form element for the new password needs to
        // be named "password" and why there's a hidden element named "login"
        // in the smarty template.)
        $this->tpl_data['username'] = $this->_username;
        $this->form->addFormRule(array(&$this, '_validate'));
    }


    /**
     * _validate validates the data that was submitted by the user. Validate
     * for password-reset only checks that a username was submitted, not that
     * it's valid, in order to prevent brute force phishing attacks.
     *
     * @param array $values The values that were submitted to the page.
     *
     * @return array (possibly empty) of errors
     */
    function _validate($values)
    {
        $this->_user = \User::factory($this->_username);
        $data        = $this->_user->getData();
        $plaintext   = htmlspecialchars_decode($values['password']);
        $confirm     = htmlspecialchars_decode($values['confirm']);

        try {
            new \Password($plaintext);
        } catch (\InvalidArgumentException $e) {
            $this->tpl_data['error_message'] = $e->getMessage();
            return array('password' => $e->getMessage());
        }

        if (!$this->_user->isPasswordDifferent($plaintext)) {
            $error_msg = "You cannot keep the same password.";
            $this->tpl_data['error_message'] = $error_msg;
            return array('password' => $error_msg);
        }

        // Ensure that the password is not the user's email
        // Otherwise an exception is thrown in User.class::updatePassword
        // TODO This validation should be done on the front-end too.
        if ($plaintext === $data['Email']) {
            $error_msg = "You cannot use your email as your password.";
            $this->tpl_data['error_message'] = $error_msg;
            return array('password' => $error_msg);
        }

        // Ensure that the password is not the user's username
        // TODO This validation should be done on the front-end too.
        if ($plaintext === $this->_username) {
            $error_msg = "You cannot use your username as your password.";
            $this->tpl_data['error_message'] = $error_msg;
            return array('password' => $error_msg);
        }

        // Ensure that the password and confirm password fields match.
        // TODO This validation should be done on the front-end too.
        if ($plaintext !== $confirm) {
            $error_msg = "The passwords do not match.";
            $this->tpl_data['error_message'] = $error_msg;
            return array('password' => $error_msg);
        }

        return array();
    }

    /**
     * _process updates the password of the user and then redirects to the
     * dashboard.
     *
     * @param array $values The values that were submitted to the page.
     *
     * @return void
     */
    function _process($values)
    {
        $this->_user->updatePassword(
            new \Password(
                htmlspecialchars_decode($values['password'])
            )
        );

        // The password is no longer expired after updating it.
        unset($_SESSION['PasswordExpiredForUser']);

        // Redirect to the dashboard
        header("HTTP/1.1 307 Temporary Redirect");
        header("Location: /");
        // Hard exiting is still always wrong but still required for now.
        exit(0);
    }

}
