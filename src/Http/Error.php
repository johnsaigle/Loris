<?php
/**
 * This file implements a HTTP error, a simple wrapper which provide
 * a html response using smarthy templates. It rely on the Diactoros implementation
 * of the Response object to provide the reason phrase according to the status code.
 *
 * PHP Version 7
 *
 * @category PSR7
 * @package  Http
 * @author   Xavier Lecours Boucher <xavier.lecoursboucher@mcgill.ca>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://www.github.com/aces/Loris/
 *
 * @see https://www.php-fig.org/psr/psr-7/
 */
namespace LORIS\Http;

use \Laminas\Diactoros\Response\HtmlResponse;
use \Psr\Http\Message\ServerRequestInterface;

/**
 * A html representation of a http error
 *
 * @category PSR7
 * @package  Http
 * @author   Xavier Lecours Boucher <xavier.lecoursboucher@mcgill.ca>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://www.github.com/aces/Loris/
 */
class Error extends HtmlResponse
{
    /**
     * Takes the status code and and use the Laminas\Response constructor to provide
     * the approcriate reason phrase. It also add the appropriate body using
     * smarty templates based on status code.
     *
     * @param ServerRequestInterface $request this HTTP request.
     * @param int                    $status  The HTTP status code to use.
     * @param string                 $message A value to pass to smarty template
     */
    public function __construct(
        ServerRequestInterface $request,
        int $status,
        string $message = ''
    ) {
        $uri     = $request->getURI();
        $baseurl = $uri->getScheme() .'://'. $uri->getAuthority();

        $tpl_data = [];

        $lorisInstance  = $request->getAttribute('loris');
        $user  = $request->getAttribute('user') ?? new \LORIS\AnonymousUser();
        

        // Add a link to the issue tracker as long as a LORIS Instance object
        // is present in the request.
        if (! $user instanceof \LORIS\AnonymousUser 
            && $lorisInstance !== null
        ) {
            // Add admistrator email.
            $contact = $lorisInstance
                ->getConfiguration()
                ->getSetting('mail')['From'];
            $tpl_data = array(
                'message' => $message,
                'baseurl' => $baseurl,
                'contact' => $contact,
            );
            // Add issue tracker data if the error is encountered by a user with
            // the correct permissions.
            $canReport = $user->hasAnyPermission(
                [
                    'issue_tracker_reporter',
                    'issue_tracker_developer',
                ]
            );
            if ($canReport) {
                $tpl_data['issueTrackerURL'] = '/issue_tracker/issue/new';
                $tpl_data['canReport']       = true;
            }
        }

        $template_file = (string) $status . '.tpl';

        $body = (new \Smarty_neurodb())
            ->assign($tpl_data)
            ->fetch($template_file);

        parent::__construct(
            (new \LORIS\Http\StringStream($body)),
            $status,
            array()
        );
    }
}
