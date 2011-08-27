<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Validator;

/**
 * @uses       \Zend\Validator\AbstractValidator
 * @uses       \Zend\Validator\Exception
 * @uses       \Zend\Validator\Hostname
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class EmailAddress extends AbstractValidator
{
    const INVALID            = 'emailAddressInvalid';
    const INVALID_FORMAT     = 'emailAddressInvalidFormat';
    const INVALID_HOSTNAME   = 'emailAddressInvalidHostname';
    const INVALID_MX_RECORD  = 'emailAddressInvalidMxRecord';
    const INVALID_SEGMENT    = 'emailAddressInvalidSegment';
    const DOT_ATOM           = 'emailAddressDotAtom';
    const QUOTED_STRING      = 'emailAddressQuotedString';
    const INVALID_LOCAL_PART = 'emailAddressInvalidLocalPart';
    const LENGTH_EXCEEDED    = 'emailAddressLengthExceeded';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID            => "Invalid type given. String expected",
        self::INVALID_FORMAT     => "'%value%' is not a valid email address. Use the basic format local-part@hostname",
        self::INVALID_HOSTNAME   => "'%hostname%' is not a valid hostname for email address '%value%'",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have a valid MX record for the email address '%value%'",
        self::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network",
        self::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
        self::QUOTED_STRING      => "'%localPart%' can not be matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for email address '%value%'",
        self::LENGTH_EXCEEDED    => "'%value%' exceeds the allowed length",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'hostname'  => '_hostname',
        'localPart' => '_localPart'
    );

    /**
     * @var string
     */
    protected $_hostname;

    /**
     * @var string
     */
    protected $_localPart;

    /**
     * Internal options array
     */
    protected $_options = array(
        'mx'       => false,
        'deep'     => false,
        'domain'   => true,
        'allow'    => Hostname::ALLOW_DNS,
        'hostname' => null
    );

    /**
     * Instantiates hostname validator for local use
     *
     * The following option keys are supported:
     * 'hostname' => A hostname validator, see Zend\Validator\Hostname
     * 'allow'    => Options for the hostname validator, see Zend\Validator\Hostname::ALLOW_*
     * 'mx'       => If MX check should be enabled, boolean
     * 'deep'     => If a deep MX check should be done, boolean
     *
     * @param array|\Zend\Config\Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof \Zend\Config\Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['mx'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['hostname'] = array_shift($options);
            }

            $options = $temp;
        }

        $options += $this->_options;
        $this->setOptions($options);
    }

    /**
     * Returns all set Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set options for the email validator
     *
     * @param array $options
     * @return \Zend\Validator\EmailAddress fluid interface
     */
    public function setOptions(array $options = array())
    {
        if (array_key_exists('messages', $options)) {
            $this->setMessages($options['messages']);
        }

        if (array_key_exists('hostname', $options)) {
            if (array_key_exists('allow', $options)) {
                $this->setHostnameValidator($options['hostname'], $options['allow']);
            } else {
                $this->setHostnameValidator($options['hostname']);
            }
        } elseif ($this->_options['hostname'] === null) {
            $this->setHostnameValidator();
        }

        if (array_key_exists('mx', $options)) {
            $this->setValidateMx($options['mx']);
        }

        if (array_key_exists('deep', $options)) {
            $this->setDeepMxCheck($options['deep']);
        }

        if (array_key_exists('domain', $options)) {
            $this->setDomainCheck($options['domain']);
        }

        return $this;
    }

    /**
     * Sets the validation failure message template for a particular key
     * Adds the ability to set messages to the attached hostname validator
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return \Zend\Validator\AbstractValidator Provides a fluent interface
     * @throws \Zend\Validator\Exception
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $this->_options['hostname']->setMessage($messageString);
            parent::setMessage($messageString);
            return $this;
        }

        if (!isset($this->_messageTemplates[$messageKey])) {
            $this->_options['hostname']->setMessage($messageString, $messageKey);
        }

        $this->_messageTemplates[$messageKey] = $messageString;
        return $this;
    }

    /**
     * Returns the set hostname validator
     *
     * @return \Zend\Validator\Hostname
     */
    public function getHostnameValidator()
    {
        return $this->_options['hostname'];
    }

    /**
     * @param \Zend\Validator\Hostname $hostnameValidator OPTIONAL
     * @param int                    $allow             OPTIONAL
     * @return void
     */
    public function setHostnameValidator(Hostname $hostnameValidator = null, $allow = Hostname::ALLOW_DNS)
    {
        if (!$hostnameValidator) {
            $hostnameValidator = new Hostname($allow);
        }

        $this->_options['hostname'] = $hostnameValidator;
        $this->_options['allow']    = $allow;
        return $this;
    }

    /**
     * Returns the set validateMx option
     *
     * @return boolean
     */
    public function getValidateMx()
    {
        return $this->_options['mx'];
    }

    /**
     * Set whether we check for a valid MX record via DNS
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $mx Set allowed to true to validate for MX records, and false to not validate them
     * @return \Zend\Validator\EmailAddress Fluid Interface
     */
    public function setValidateMx($mx)
    {
        $this->_options['mx'] = (bool) $mx;
        return $this;
    }

    /**
     * Returns the set deepMxCheck option
     *
     * @return boolean
     */
    public function getDeepMxCheck()
    {
        return $this->_options['deep'];
    }

    /**
     * Set whether we check MX record should be a deep validation
     *
     * @param boolean $deep Set deep to true to perform a deep validation process for MX records
     * @return \Zend\Validator\EmailAddress Fluid Interface
     */
    public function setDeepMxCheck($deep)
    {
        $this->_options['deep'] = (bool) $deep;
        return $this;
    }

    /**
     * Returns the set domainCheck option
     *
     * @return unknown
     */
    public function getDomainCheck()
    {
        return $this->_options['domain'];
    }

    /**
     * Sets if the domain should also be checked
     * or only the local part of the email address
     *
     * @param boolean $domain
     * @return \Zend\Validator\EmailAddress Fluid Interface
     */
    public function setDomainCheck($domain = true)
    {
        $this->_options['domain'] = (boolean) $domain;
        return $this;
    }

    /**
     * Returns if the given host is reserved
     *
     * The following addresses are seen as reserved
     * '0.0.0.0/8', '10.0.0.0/8', '127.0.0.0/8'
     * '172.16.0.0/12'
     * '198.18.0.0/15'
     * '128.0.0.0/16', '169.254.0.0/16', '191.255.0.0/16', '192.168.0.0/16'
     * '192.0.0.0/24', '192.0.2.0/24', '192.88.99.0/24', '198.51.100.0/24', '203.0.113.0/24', '223.255.255.0/24'
     * '224.0.0.0/4', '240.0.0.0/4'
     *
     * @param string $host
     * @return boolean Returns false when minimal one of the given addresses is not reserved
     */
    private function _isReserved($host){
        if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $host)) {
            $host = gethostbynamel($host);
        } else {
            $host = array($host);
        }

        if (empty($host)) {
            return false;
        }

        foreach ($host as $server) {
                 // Search for 0.0.0.0/8, 10.0.0.0/8, 127.0.0.0/8
            if (!preg_match('/^(0|10|127)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $host) &&
                // Search for 172.16.0.0.12
                !preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $host) &&
                // Search for 198.18.0.0/15
                !preg_match('/^198\.(1[8-9])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $host) &&
                // Search for 128.0.0.0/16, 169.254.0.0/16, 191.255.0.0/16, 192.168.0.0/16
                !preg_match('/^(128\.0|169\.254|191\.255|192\.168)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $host) &&
                // Search for 192.0.0.0/24, 192.0.2.0/24, 192.88.99.0/24, 198.51.100.0/24, 203.0.113.0/24, 223.255.255.0/24
                !preg_match('/^(192\.0\.(0|2)|192\.88\.99|198\.51\.100|203\.0\.113|223\.255\.255)\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))$/', $host) &&
                // Search for 224.0.0.0/4, 240.0.0.0/4
                !preg_match('/^(2(2[4-9]|[3-4][0-9]|5[0-5]))(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $host)) {
                return false;
            }
        }
    }

    /**
     * Internal method to validate the local part of the email address
     *
     * @return boolean
     */
    private function _validateLocalPart()
    {
        // First try to match the local part on the common dot-atom format
        $result = false;

        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
        if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->_localPart)) {
            $result = true;
        } else {
            // Try quoted string format

            // Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
            // qtext: Non white space controls, and the rest of the US-ASCII characters not
            //   including "\" or the quote character
            $noWsCtl = '\x01-\x08\x0b\x0c\x0e-\x1f\x7f';
            $qtext   = $noWsCtl . '\x21\x23-\x5b\x5d-\x7e';
            $ws      = '\x20\x09';
            if (preg_match('/^\x22([' . $ws . $qtext . '])*[$ws]?\x22$/', $this->_localPart)) {
                $result = true;
            } else {
                $this->_error(self::DOT_ATOM);
                $this->_error(self::QUOTED_STRING);
                $this->_error(self::INVALID_LOCAL_PART);
            }
        }

        return $result;
    }

    /**
     * Internal method to validate the servers MX records
     *
     * @return boolean
     */
    private function _validateMXRecords()
    {
        $mxHosts = array();
        $result = getmxrr($this->_hostname, $mxHosts);
        if (!$result) {
            $this->_error(self::INVALID_MX_RECORD);
        } else if ($this->_options['deep']) {
            $validAddress = false;
            $reserved     = true;
            foreach ($mxHosts as $hostname) {
                $res = $this->_isReserved($hostname);
                if (!$res) {
                    $reserved = false;
                }

                if (!$res
                    && (checkdnsrr($hostname, "A")
                    || checkdnsrr($hostname, "AAAA")
                    || checkdnsrr($hostname, "A6"))) {
                    $validAddress = true;
                    break;
                }
            }

            if (!$validAddress) {
                $result = false;
                if ($reserved) {
                    $this->_error(self::INVALID_SEGMENT);
                } else {
                    $this->_error(self::INVALID_MX_RECORD);
                }
            }
        }

        return $result;
    }

    /**
     * Internal method to validate the hostname part of the email address
     *
     * @return boolean
     */
    private function _validateHostnamePart()
    {
        $hostname = $this->_options['hostname']->setTranslator($this->getTranslator())
                         ->isValid($this->_hostname);
        if (!$hostname) {
            $this->_error(self::INVALID_HOSTNAME);

            // Get messages and errors from hostnameValidator
            foreach ($this->_options['hostname']->getMessages() as $code => $message) {
                $this->_messages[$code] = $message;
            }

            foreach ($this->_options['hostname']->getErrors() as $error) {
                $this->_errors[] = $error;
            }
        } else if ($this->_options['mx']) {
            // MX check on hostname
            $hostname = $this->_validateMXRecords();
        }

        return $hostname;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid email address
     * according to RFC2822
     *
     * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
     * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $matches = array();
        $length  = true;
        $this->_setValue($value);

        // Split email address up and disallow '..'
        if ((strpos($value, '..') !== false) or
            (!preg_match('/^(.+)@([^@]+)$/', $value, $matches))) {
            $this->_error(self::INVALID_FORMAT);
            return false;
        }

        $this->_localPart = $matches[1];
        $this->_hostname  = $matches[2];

        if ((strlen($this->_localPart) > 64) || (strlen($this->_hostname) > 255)) {
            $length = false;
            $this->_error(self::LENGTH_EXCEEDED);
        }

        // Match hostname part
        if ($this->_options['domain']) {
            $hostname = $this->_validateHostnamePart();
        }

        $local = $this->_validateLocalPart();

        // If both parts valid, return true
        if ($local && $length) {
            if (($this->_options['domain'] && $hostname) || !$this->_options['domain']) {
                return true;
            }
        }

        return false;
    }
}
