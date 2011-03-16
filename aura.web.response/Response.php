<?php

namespace aura\web;

/**
 * 
 * Generic HTTP response object for sending headers, cookies, and content.
 * 
 * This is a fluent class; the set() methods can be chained together like so:
 * 
 * {{code: php
 *     $response->setStatusCode(404)
 *              ->setHeader('X-Foo', 'Bar')
 *              ->setCookie('baz', 'dib')
 *              ->setContent('Page not found.')
 *              ->display();
 * }}
 * 
 * @package aura.web
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @todo Add charset param so that headers get sent with right encoding?
 * 
 */
class Response extends AbstractResponse
{
    /**
     * 
     * Whether or not cookies should default being sent by HTTP only.
     * 
     * @var bool
     * 
     */
    protected $cookies_httponly = true;
    
    
    public function __construct(aura\Mime\Utility $mime_utility)
    {
        parent::__construct($mime_utility);
    }
    
    /**
     * 
     * Sends all headers and cookies, then returns the body.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        $this->sendHeaders();
        
        // cast to string to avoid fatal error when returning nulls
        return (string) $this->content;
    }
    
    /**
     * 
     * By default, should cookies be sent by HTTP only?
     * 
     * @param bool $flag True to send by HTTP only, false to send by any
     * method.
     * 
     * @return aura\web\Response This response object.
     * 
     */
    public function setCookiesHttponly($flag)
    {
        $this->cookies_httponly = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Should the response disable HTTP caching?
     * 
     * When true, the response will send these headers:
     * 
     * {{code:
     *     Pragma: no-cache
     *     Cache-Control: no-store, no-cache, must-revalidate
     *     Cache-Control: post-check=0, pre-check=0
     *     Expires: 1
     * }}
     * 
     * @param bool $flag When true, disable browser caching. Default is true.
     * 
     * @see redirectNoCache()
     * 
     * @return void
     * 
     */
    public function setNoCache($flag = true)
    {
        if ($flag) {
            $this->headers['Pragma']        = 'no-cache';
            $this->headers['Cache-Control'] = array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
            );
            $this->headers['Expires']       = '1';
        } else {
            unset($this->headers['Pragma']);
            unset($this->headers['Cache-Control']);
            unset($this->headers['Expires']);
        }
    }
    
    /**
     * 
     * Sends all headers and cookies, then prints the response content.
     * 
     * @return void
     * 
     */
    public function display()
    {
        $this->sendHeaders();
        echo $this->content;
    }
    
    /**
     * 
     * Issues an immediate "Location" redirect.  Use instead of display()
     * to perform a redirect.  You should die() or exit() after calling this.
     * 
     * @param aura\XXX\Uri|string $href The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '302 Found'.
     * 
     * @return void
     * 
     * @throws aura\web\Exception No URI.
     * 
     */
    public function redirect($href, $code = '302')
    {
        if ($href instanceof aura\XXX\Uri) { // xxx
            // make $href a string
            $href = $href->get(true);
        } else if (strpos($href, '://') !== false) {
            // external link, protect against header injections
            $href = str_replace(array("\r", "\n"), '', $href);
        }
        
        // kill off all output buffers
        while(@ob_end_clean());
        
        // make sure there's actually an href
        $href = trim($href);
        if (! $href) {
            throw new Exception('No URI; cannot redirect.');
        }
        
        // set the status code
        $this->setStatusCode($code);
        
        // set the redirect location 
        $this->setHeader('Location', $href);
        
        // clear the response body
        $this->content = null;
        
        // save the session
        session_write_close();
        
        // send the response directly -- done.
        $this->display();
    }
    
    /**
     * 
     * Redirects to another page and action after disabling HTTP caching.
     * This effectively implements the "POST-Redirect-GET" pattern (also known
     * as the "GET after POST").
     * 
     * The redirect() method is often called after a successful POST
     * operation, to show a "success" or "edit" page. In such cases, clicking
     * clicking "back" or "reload" will generate a warning in the
     * browser allowing for a possible re-POST if the user clicks OK.
     * Typically this is not what you want.
     * 
     * In those cases, use redirectNoCache() to turn off HTTP caching, so
     * that the re-POST warning does not occur.
     * 
     * This method calls [[aura\web\Response::setNoCache() | ]] to disable
     * caching.
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '303 See Other'.
     * 
     * @return void
     * 
     * @see <http://www.theserverside.com/tt/articles/article.tss?l=RedirectAfterPost>
     * 
     * @see setNoCache()
     * 
     */
    public function redirectNoCache($spec, $code = '303')
    {
        $this->setNoCache();
        return $this->redirect($spec, $code);
    }
    
    /**
     * 
     * Sends all headers and cookies.
     * 
     * @return void
     * 
     * @throws aura\web\Exception_HeadersSent if headers have
     * already been sent.
     * 
     */
    protected function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new Exception_HeadersSent($file, $line);
        }
        
        // build the full status header string. The values have already been
        // sanitized by setStatus() and setStatusText().
        $status = "HTTP/{$this->version} {$this->status_code}";
        if ($this->status_text) {
            $status .= " {$this->status_text}";
        }
        
        // send the status header
        header($status, true, $this->status_code);
        
        // send each of the remaining headers
        foreach ($this->headers as $key => $list) {
            
            // skip empty keys, keys have been sanitized by setHeader.
            if (! $key) {
                continue;
            }
            
            // send each value for the header
            foreach ((array) $list as $val) {
                // we don't need full MIME escaping here, just sanitize the
                // value by stripping CR and LF chars
                $val = str_replace(array("\r", "\n"), '', $val);
                header("$key: $val");
            }
        }
        
        // send each of the cookies
        foreach ($this->cookies as $key => $val) {
            
            // was httponly set for this cookie?  if not, use the default.
            $httponly = ($val['httponly'] === null)
                ? $this->cookies_httponly
                : (bool) $val['httponly'];
            
            // try to allow for times not in unix-timestamp format
            if (! is_numeric($val['expires'])) {
                $val['expires'] = strtotime($val['expires']);
            }
            
            // actually set the cookie
            setcookie(
                $key,
                $val['value'],
                (int) $val['expires'],
                $val['path'],
                $val['domain'],
                (bool) $val['secure'],
                (bool) $httponly
            );
        }
    }
}