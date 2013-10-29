<?php

namespace Flare\Service;

use Flare\Object\Json;
use Flare\Flare as F;
use Flare\Service;

/**
 * 
 * @author anthony
 * 
 */
class Facebook extends Service
{
    /**
     * 
     * @var string
     */
    const API_HOST = 'https://graph.facebook.com/';

    /**
     * 
     * @var string
     */
    const HOST = 'https://www.facebook.com/';

    /**
     * 
     * @var string
     */
    private $_accessToken;

    /**
     * 
     * @var string
     */
    private $_appId;

    /**
     * 
     * @var string
     */
    private $_appSecret;

    /**
     * 
     * @var boolean
     */
    private $_fileUploadSupport;

    /**
     * 
     * @var array
     */
    private $_signedRequest = array();

    /**
     * 
     * @var string
     */
    private $_user;

    /**
     * 
     * @access protected
     * @param array $params
     * @return void
     */
    protected function init(array $params)
    {
        $this->setAppId($params['app_id']);
        $this->setAppSecret($params['app_secret']);
        $this->setFileUpload($params['file_upload']);
        $this->setAccessToken();
    }

    /**
     * 
     * @param string $userId
     * @return void
     */
    private function _setUser($userId = null)
    {
        if ($userId) {
            $this->_user = $userId;
            F::$session->set('fb_'.$this->_appId.'_user', $userId);
        } else {
            $this->_user = F::$session->get('fb_'.$this->_appId.'_user');
        }
    }

    /**
     * 
     * @return void
     */
    private function _parseSignedRequest($s_request)
    {
        if (!$s_request) {
            $this->_setUser();
            return;
        }

        list($encoded_sig, $payload) = explode('.', $s_request, 2); 
        $sig = $this->_base64UrlDecode($encoded_sig);
        $data = json_decode($this->_base64UrlDecode($payload), true);
        $this->_signedRequest = $data;

        if (!isset($data['user_id'])) {
            $this->_setUser();
        } else {
            $this->_setUser($data['user_id']);
        }
    }

    /**
     * 
     * @param string $input
     * @return string
     */
    private function _base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * 
     * @param string $redirect_uri
     * @param boolean $auto_redirect
     * @return string|void
     */
    private function _connect($redirect_uri, $permission = array(), $auto_redirect = false)
    {
        $value = sha1(uniqid(rand(), true));
        $data = array(
            'client_id' => $this->_appId,
            'redirect_uri' => $redirect_uri,
            'state' => $value,
            'scope' => implode(',', $permission)
        );
        F::$session->set('fb_'.$this->_appId.'_perms', $permission);
        F::$session->set('fb_'.$this->_appId.'_state', $value);
        F::$session->set('fb_'.$this->_appId.'_uri', $data['redirect_uri']);
        $loginUrl = self::HOST.'dialog/oauth?'.http_build_query($data);
        if ($auto_redirect) {
            F::$response->setRedirect($loginUrl)->send(false);
        }
        return $loginUrl;
    }

    /**
     * 
     * @return array
     */
    public function getPermission()
    {
        return F::$session->get('fb_'.$this->_appId.'_perms');
    }

    /**
     * 
     * @param string $type
     * @param string $fbid
     * @return string
     */
    public function getImage($type = 'small', $fbid = 'me')
    {
        if ($fbid == 'me') {
            $fbid = $this->getUser();
        }
        return 'https://graph.facebook.com/'.$fbid.'/picture?type='.$type;
    }

    /**
     * 
     * @param string $redirect_uri
     * @param boolean $auto_redirect
     * @return string|void
     */
    public function reconnect($redirect_uri, $permission = array(), $auto_redirect = false)
    {
        return $this->_connect($redirect_uri, $permission, $auto_redirect);
    }

    /**
     * 
     * @param string $redirect_uri
     * @param boolean $auto_redirect
     * @return string|null
     */
    public function connect($redirect_uri, $permission = array(), $auto_redirect = false)
    {
        if (!$this->_accessToken) {
            return $this->_connect($redirect_uri, $permission, $auto_redirect);
        }
        return null;
    }

    /**
     * 
     * @return string
     */
    public function getSignedRequest()
    {
        return $this->_signedRequest;
    }


    public function feed()
    {
        
    }

    public function comment()
    {

    }

    public function like()
    {

    }
    
    /**
     * 
     * @param string $id
     * @return \Flare\Object\Json
     */
    public function getProfile($id = 'me')
    {
        $data = $this->curl
            ->setParam('access_token', $this->getAccessToken())
            ->setUrl(self::API_HOST.$id)
            ->getContentAsJson();

        if (isset($data['error'])) {
            $this->_error = $data['error'];
            return null;
        }
        return $data;
    }

    /**
     * 
     * @param string $id
     * @param array $fields
     * @return \Flare\Object\Json
     */
    public function getUserDetails($id = 'me', $fields = array())
    {
        if ($id === 'me') {
            $id = $this->getUser();
        }
        if (!$fields) {
            $fields = array('uid', 'first_name', 'last_name', 'profile_url');
        }
        $fql = 'SELECT '.implode(',', $fields).' FROM user WHERE uid = '.$id;
        $data = $this->curl
            ->setUrl(self::API_HOST.'fql')
            ->setParam('access_token', $this->getAccessToken())
            ->setParam('q', $fql)
            ->getContentAsJson();

        if (isset($data['error'])) {
            $this->_error = $data['error'];
            return null;
        }
        $data = isset($data['data']) ? end($data['data']) : array();
        return new Json($data);
    }

    /**
     * 
     * @param string $fql
     * @return \Flare\Object\Json
     */
    public function fql($fql)
    {
        return $this->curl
            ->setUrl(self::API_HOST.'fql')
            ->setParam('access_token', $this->getAccessToken())
            ->setParam('q', $fql)
            ->getContentAsJson();
    }

    /**
     * 
     * @param array $fields
     * @param int $limit
     * @param int $page
     * @param string $order
     * @return \Flare\Object\Json
     */
    public function getFriends($fields = array(), $limit = 0, $page = 0, $order = null)
    {
        if (!$fields) {
            $fields = array('uid', 'first_name', 'last_name', 'profile_url');
        }
        $fql = 'SELECT '.implode(',', $fields).' FROM user '
            .'WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = '.$this->getUser().')';
        if ($limit) {
            $page = (int) ($page <= 1 || !$page ? 0 : ($page - 1));
            $fql .= ' LIMIT '.($page * $limit).','.(int) $limit;
        }
        if ($order) {
            $fql .= ' ORDER BY '.(string) $order;
        }
        $data = $this->curl
            ->setUrl(self::API_HOST.'fql')
            ->setParam('access_token', $this->getAccessToken())
            ->setParam('q', $fql)
            ->getContentAsJson();

        if (isset($data['error'])) {
            $this->_error = $data['error'];
            return null;
        }
        $data = isset($data['data']) ? $data['data'] : array();
        return new Json($data);
    }

    /**
     * 
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->_error);
    }

    /**
     * 
     * @return array
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 
     * @param string $token
     * @return \Flare\Service\Facebook
     */
    public function setAccessToken($token = null)
    {
        $code = F::$request->get('code');
        $state = F::$request->get('state');
        if ($token) {
            $this->_accessToken = (string) $token;
            F::$session->set('fb_'.$this->_appId.'_token', $this->_accessToken);
        } elseif (F::$session->has('fb_'.$this->_appId.'_token')) {
            $this->_accessToken = F::$session->get('fb_'.$this->_appId.'_token');
        } elseif (($code && $state) 
            && strcmp($state, F::$session->get('fb_'.$this->_appId.'_state')) === 0) 
        {
            $result = $this->curl
                        ->setParam('code', $code)
                        ->setParam('client_id', $this->_appId)
                        ->setParam('client_secret', $this->_appSecret)
                        ->setParam('redirect_uri', F::$session->get('fb_'.$this->_appId.'_uri'))
                        ->setUrl(self::API_HOST.'oauth/access_token')
                        ->getContent();
            
            if ($this->curl->hasError()) {
                show_error($this->curl->getError());
            }
            
            parse_str($result, $params);
            $this->_accessToken = $params['access_token'];
            F::$session->set('fb_'.$this->_appId.'_token', $params['access_token']);
        }

        $s_request = F::$request->param('signed_request');
        if (!$s_request) {
            $s_request = F::$session->get('fb_'.$this->_appId.'_signed_request');
        } else {
            F::$session->set('fb_'.$this->_appId.'_signed_request', $s_request);
        }
        $this->_parseSignedRequest($s_request);
        return $this;
    }

    /**
     * 
     * @param boolean $upload
     * @return \Flare\Service\Facebook
     */
    public function setFileUpload($upload)
    {
        $this->_fileUploadSupport = (boolean) $upload;
        return $this;
    }

    /**
     * 
     * @param string $id
     * @return \Flare\Service\Facebook
     */
    public function setAppId($id)
    {
        $this->_appId = (string) $id;
        return $this;
    }

    /**
     * 
     * @param string $secret
     * @return \Flare\Service\Facebook
     */
    public function setAppSecret($secret)
    {
        $this->_appSecret = (string) $secret;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getAppId()
    {
        return $this->_appId;
    }

    /**
     * 
     * @return string
     */
    public function getAppSecret()
    {
        return $this->_appSecret;
    }

    /**
     * 
     * @return string
     */
    public function getAccessToken()
    {
        if (!$this->_accessToken) {
            return F::$session->get('fb_'.$this->_appId.'_token');
        }
        return $this->_accessToken;
    }

    /**
     * 
     * @return string|null
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 
     * @param string $userId
     * @return \Flare\Service\Facebook
     */
    public function setUser($userId)
    {
        $this->_user = (string) $userId;
        return $this;
    }
}