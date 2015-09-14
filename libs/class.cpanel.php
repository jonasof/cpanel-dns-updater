<?php
/**
 * cPanel PHP API
 *
 * This is an API for control some of the cPanel functions such as add:
 * * Email manipulation (create accounts, delete accounts, modify quota, etc)
 *
 * cPanel Working version: 11.24.5-STABLE
 *
 * @author Gerardo Ortiz V.
 * @copyright (c) 2010 MB Works, Inc. 
 * @package cPanelAPI
 */
class cPanel
{
	/**
	 * cPanel Authorization hash
	 *
	 * @var string
	 */
	private $_auth = '';
	
	/**
	 * cPanel hostname
	 *
	 * @var string
	 */
	private $_host = '';
	
	/**
	 * cPanel_Email object
	 *
	 * @var cPanel_Email
	 */
	private $_object_email;
	
	/**
	 * cPanel password
	 *
	 * @val string
	 */
	private $_password = '';
	
	/**
	 * cPanel full path
	 *
	 * @var string
	 */
	private $_path = '';
	
	/**
	 * cPanel default port
	 *
	 * @var integer
	 */
	private $_port = 2082;
	
	/**
	 * cPanel use SSL
	 *
	 * @var bool
	 */
	private $_ssl = false;
	
	/**
	 * cPanel theme
	 *
	 * @var string
	 */
	private $_theme = 'x3';
	
	/**
	 * cPanel username
	 *
	 * @var string
	 */
	private $_username = '';
	
	/**
	 * Creates an object to manipulate cPanel
	 * @param string $host cPanel host without leading http://
	 * @param string $username cPanel username
	 * @param string $password cPanel password
	 * @param integer $port cPanel port, default to 2082. Change to 2083 if using SSL
	 * @param string $theme cPanel theme, (forward compatibility- 'x' theme currently required)
	 * @param bool $ssl False for http (default), true for SSL (requires OpenSSL)
	 * @return cPanel
	 */
	public function __construct($host, $username, $password, $port=2082, $theme='x3', $ssl=false)
	{
		if ((!$host || empty($host))
		||  (!$username || empty($username))
		||  (!$password || empty($password))) {
			return false;
		}
		
		$this->_auth     = base64_encode($username . ':' . $password);
		$this->_host     = $host;
		$this->_port     = intval($port);
		$this->_theme    = $theme;
		$this->_ssl      = $ssl ? 'ssl://' : '';
		$this->_password = $password;
		$this->_username = $username;
		$this->_path     = '/frontend/' . $theme . '/';
	}
	
	/**
	 * Returns the email Object
	 *
	 * @return cPanel_Email
	 */
	public function email()
	{
		if (!$this->_object_email) {
			$this->_object_email = new cPanel_Email($this->_host, $this->_username, $this->_password, $this->_port, $this->_theme, $this->_ssl);
		}
		
		return $this->_object_email;
	}
	
	/**
	 * Retrieve contact email address.
	 *
	 * Returns the contact email address listed in cPanel.
	 * @return string
	 */
	public function getContactEmail()
	{
		$email    = array();
		$response = $this->sendRequest('contact/index.html');
		
		if (!$response) {
			return '';
		}
		
		preg_match_all('/<input.*?value\\s*=\\s*"?([^\\s>"]*)/i', $response, $matches, PREG_PATTERN_ORDER);
		
		// Search the one with the 'email' keyword
		$totalMatches = count($matches[0]);
		for ($i=0; $i<=$totalMatches; $i++) {
			if (strpos($matches[0][$i], 'email') && !strpos($matches[0][$i], 'second_email')) {
				return $matches[1][$i];
			}
		}
		
		return '';
	}

	/**
	 * Modify contact email address
	 *
	 * Returns true on success or false on failure.
	 * @param string new contact email address
	 * @return string
	 */
	public function setContactEmail($email)
	{
		if (!$email || empty($email)) {
			return false;
		}

		$response = $this->sendRequest('contact/saveemail.html', array('email' => $email));
		if(strpos($response, 'has been')) {
			return true;
		}
		
		return false;
	}
	
    /**
	 * Change cPanel's password
	 *
	 * Returns true on success or false on failure.
	 * The cPanel object is no longer usable after changing the password.
	 * @param string $password new password
	 * @return bool
	 */
	public function setPassword($password)
	{
		if (!$password || empty($password)) {
			return false;
		}
		
		$response = $this->sendRequest('passwd/changepass.html', array('oldpass' => $this->_password,
																	   'newpass' => $password));
		// Watch the response and define if we succesfully managed to change
		// the password
		if(($response !== false) && strpos($response, 'has been') && !strpos($response, 'could not')) {
			return true;
		}
		
		return false;
	}

	/**
	 * Sends a request to cPanel
	 *
	 * Returns the response or false on failure
	 * @param string $url cPanel URL to send the data
	 * @param array $data Data to send
	 * @return mixed
	 */
	public function sendRequest($url, $data='')
	{
		$url = $this->_path . $url;
		
		// If we get more values at data, build the url
		if(is_array($data)) {
			$url = $url . '?';
			foreach($data as $key=>$value) {
				$url .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$url = substr($url, 0, -1);
		}
		
		$response = '';
		$fp = fsockopen($this->_ssl . $this->_host, $this->_port);
		if(!$fp) {
			return false;
		}
		
		$out  = 'GET ' . $url . ' HTTP/1.0' . "\r\n";
		$out .= 'Authorization: Basic ' . $this->_auth . "\r\n";
		$out .= 'Connection: Close' . "\r\n\r\n";
		fwrite($fp, $out);
		
		while (!feof($fp)) {
			$response .= @fgets($fp);
		}
		
                
		fclose($fp);
		
		// Could not log in
		if(strpos($response, 'Login Attempt Failed!')) {
			return false;
		}
		
		return $response;
	}
}

class cPanel_Email
{
	/**
	 * cPanel Authorization hash
	 *
	 * @var string
	 */
	private $_auth = '';
	
	/**
	 * cPanel hostname
	 *
	 * @var string
	 */
	private $_host = '';
	
	/**
	 * cPanel_Email object
	 *
	 * @var cPanel_Email
	 */
	private $_object_email;
	
	/**
	 * cPanel password
	 *
	 * @val string
	 */
	private $_password = '';
	
	/**
	 * cPanel full path
	 *
	 * @var string
	 */
	private $_path = '';
	
	/**
	 * cPanel default port
	 *
	 * @var integer
	 */
	private $_port = 2082;
	
	/**
	 * cPanel use SSL
	 *
	 * @var bool
	 */
	private $_ssl = false;
	
	/**
	 * cPanel theme
	 *
	 * @var string
	 */
	private $_theme = 'x3';
	
	/**
	 * cPanel username
	 *
	 * @var string
	 */
	private $_username = '';
	
	/**
	 * Creates an object to manipulate cPanel
	 * @param string $host cPanel host without leading http://
	 * @param string $username cPanel username
	 * @param string $password cPanel password
	 * @param integer $port cPanel port, default to 2082. Change to 2083 if using SSL
	 * @param string $theme cPanel theme, (forward compatibility- 'x' theme currently required)
	 * @param bool $ssl False for http (default), true for SSL (requires OpenSSL)
	 * @return cPanel
	 */
	public function __construct($host, $username, $password, $port=2082, $theme='x3', $ssl=false)
	{
		if ((!$host || empty($host))
		||  (!$username || empty($username))
		||  (!$password || empty($password))) {
			return false;
		}
		
		$this->_auth     = base64_encode($username . ':' . $password);
		$this->_host     = $host;
		$this->_port     = intval($port);
		$this->_theme    = $theme;
		$this->_ssl      = $ssl ? 'ssl://' : '';
		$this->_password = $password;
		$this->_username = $username;
		$this->_path     = '/frontend/' . $theme . '/';
	}
	
	/**
	 * Create email account in cPanel
	 *
	 * Returns true on success or false on failure.
	 * @param string $email email account
	 * @param string $password email account password
	 * @param integer $quota quota for email account in megabytes
	 * @param string $domain domain for email account 
	 * @return bool
	 */
	public function create($email, $password, $quota, $domain='')
	{
		if ((!$email || empty($email))
		||  (!$password || empty($password))
		||  (!$quota || empty($quota))) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/doaddpop.html', array('email'    => $email,
																   'domain'   => $domain,
																   'password' => $password,
																   'quota'    => intval($quota)));

		if(!$response || strpos($response, 'failure') || strpos($response, 'already exists')) {
			return false;
		}
		
		return true;
	}
	

	/**
	 * Delete email account
	 *
	 * Permanenetly removes POP3 account. Returns true on success or false on failure.
	 * @param string $email email account
	 * #param string $domain domain for email account
	 * @return bool
	 */
	public function delete($email, $domain='')
	{
		if (!$email || empty($email)) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/realdelpop.html', array('email'  => $email,
																	 'domain' => $domain));
		if($response !== false && strpos($response, 'success')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Delete email autoresponder
	 *
	 * Deletes autoresponder for email account if it exists, and returns true.
	 * @return bool
	 */
	public function deleteAutoResponder($email, $domain='')
	{
		if (!$email || empty($email)) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/dodelautores.html?email=' . $email . '@' . $domain);
		return (!$response) ? false : true;
	}
	
	/**
	 * Delete email forwarder
	 *
	 * Permanently removes the account's email forwarder and returns true.
	 * @param string $forwarder forwarding address to delete
	 * @return bool
	 */
	public function deleteForwarder($email, $forwarder, $domain='')
	{	
		if ((!$email || empty($email))
		&&  (!$forward || empty($forward))) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/dodelfwd.html', array('email' => $email.'@'.$domain.'='.$forwarder));
		
		if (!$response) {
			return false;
		}
		
		return true;
	}
	
	/**
     * List email forwarders
     *
     * Returns a numerically-indexed array of forwarders for the email account. Returns an empty array if there are no forwarders.
	 * @param string $email email account
	 * #param string $domain domain for email account
     * @return array
    */
	public function getForwarders($email, $domain='')
	{
		if (!$email || empty($email)) {
			return array();
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$forwarders = array();
		$response   = $this->sendRequest('mail/fwds.html?domain='.$domain.'&itemsperpage=5000');
		
		if (!$response) {
			return array();
		}
		
		preg_match_all('/dodelfwdconfirm.html\?.*email='.$email.'%40'.$domain.'([^\\s>].*)"/', $response, $forwarders);
		if (empty($forwarders) || !isset($forwarders[1])) {
			return array();
		}
		
		$list = array();
		foreach($forwarders[1] as $forwarder) {
			$exploded = explode('&', $forwarder);
			
			if (!isset($exploded[1])) {
				continue;
			}
			
			$values = explode('=', $exploded[1]);
			if (isset($values[0]) && isset($values[1]) && $values[0] == 'emaildest') {
				$list[] = str_replace('%40', '@', $values[1]);
			}
		}

		return $list;
	}
	
	/**
	 * Get account storage quota
	 *
	 * Returns amount of disk space allowed for email account in megabytes.
	 * @param string $email email account
	 * #param string $domain domain for email account
	 * @return mixed
	 */
	public function getQuota($email, $domain='')
	{
		if (!$email || empty($email)) {
			return 0;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$quota    = array();
		$response = $this->sendRequest('mail/editquota.html', array('email'  => $email,
										                            'domain' => $domain));
		if (!$response) {
			return 0;
		}
		
		preg_match('/quota" value="([^"]*)/', $response, $quota);
		return ($quota[1] == 0) ? 'Unlimited' : $quota[1] . ' MB';
	}
	
	/**
	 * Get space used by account
	 *
	 * Returns the amount of disk space used by email account in megabytes.
	 * @param string $email email account
	 * #param string $domain domain for email account
	 * @return string
	 */
	public function getUsedSpace($email, $domain='')
	{
		if (!$email || empty($email)) {
			return 0;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		// Fix: adding the domain would increase the email result if many emails
		$response = $this->sendRequest('mail/pops.html?domain='.$domain.'&itemsperpage=5000');
		$matches  = array();
		$values   = array();
		
		if (!$response) {
			return 0;
		}

		preg_match_all('/>([^\\s>]*@'.$domain.')</i', $response, $matches, PREG_PATTERN_ORDER);
		preg_match_all('/>([\d].*[GMK]B|None)<\/d/i', $response, $values, PREG_PATTERN_ORDER);
		
		if (empty($matches) || !isset($matches[1]) || !isset($values[1])) {
			return 0;
		}
		
		foreach ($matches[1] as $key => $match_email) {
			if ($match_email == $email . '@' . $domain) {
				return (!isset($values[1][$key])) ? 0 : $values[1][$key];
			}
		}
		
		return 0;
	}

	/**
	 * Create email autoresponder
	 *
	 * Returns true on success or false on failure.
	 * @param string $from from email address
	 * @param string $subject email subject line
	 * @param string $charset character set
	 * @param bool $html true for HTML email
	 * @param string $body body of email message
	 * @return bool
	 */
	public function setAutoResponder($email, $from, $subject, $body, $domain='', $html=false, $charset='utf-8')
	{
		if ((!$email || empty($email))
		||  (!$from || empty($from))
		||  (!$subject || empty($subject))
		||  (!$body || empty($body))) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->HTTP->getData('mail/doaddars.html', array('email'   => $email,
																	 'domain'  => $domain,
																	 'from'    => $from,
																	 'subject' => $subject,
																	 'html'    => (bool)$html,
																	 'body'    => $body,
																	 'charset' => $charset));
		
		if($response !== false && strpos($response, 'success') && !strpos($response, 'failure')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Create email forwarder
	 *
	 * Returns true on success or false on failure.
	 * @param string $email email account
	 * @param string $forward forwarding address
	 * @param string $domain domain for email account
	 * @return bool
	 */
	public function setForwarder($email, $forward, $domain='')
	{
		if ((!$email || empty($email))
		&&  (!$forward || empty($forward))) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/doaddfwd.html', array('email'    => $email,
															       'domain'   => $domain,
																   'fwdemail' => $forward));

		if($response !== false && strpos($response, 'redirected')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Modify account storage quota
	 *
	 * Returns true on success or false on failure.
	 * @param string $email email account
	 * @param integer $quota quota for email account in megabytes
	 * @param string $domain domain for email account
	 * @return bool
	 */
	public function setQuota($email, $quota=0, $domain='')
	{
		if (!$email || empty($email)) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$response = $this->sendRequest('mail/doeditquota.html', array('email'  => $email,
										                              'domain' => $domain,
																	  'quota'  => intval($quota)));
		if($response !== false && strpos($response, 'success')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Change email account password
	 *
	 * Returns true on success or false on failure.
	 * @param string $email email account
	 * @param string $password email account password
	 * @param string $domain domain for email account
	 * @return bool
	 */
	public function setPassword($email, $password, $domain='')
	{
		if ((!$email || empty($email))
		||  (!$password || empty($password))) {
			return false;
		}
		
		if (empty($domain)) {
			$domain = $this->_host;
		}
		
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['password'] = $password;
		$response = $this->sendRequest('mail/dopasswdpop.html', array('email'    => $email,
																	  'domain'   => $domain,
																	  'password' => $password));
		
		if($response !== false && strpos($response, 'success') && !strpos($response, 'failure')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sends a request to cPanel
	 *
	 * Returns the response or false on failure
	 * @param string $url cPanel URL to send the data
	 * @param array $data Data to send
	 * @return mixed
	 */
	private function sendRequest($url, $data='')
	{
		$url = $this->_path . $url;
		
		// If we get more values at data, build the url
		if(is_array($data)) {
			$url = $url . '?';
			foreach($data as $key=>$value) {
				$url .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$url = substr($url, 0, -1);
		}
		
		$response = '';
		$fp = fsockopen($this->_ssl . $this->_host, $this->_port);
		if(!$fp) {
			return false;
		}
		
		$out  = 'GET ' . $url . ' HTTP/1.0' . "\r\n";
		$out .= 'Authorization: Basic ' . $this->_auth . "\r\n";
		$out .= 'Connection: Close' . "\r\n\r\n";
		fwrite($fp, $out);
		
		while (!feof($fp)) {
			$response .= @fgets($fp);
		}
		
		fclose($fp);
		
		// Could not log in
		if(strpos($response, 'Login Attempt Failed!')) {
			return false;
		}
		
		return $response;
	}
}


?>