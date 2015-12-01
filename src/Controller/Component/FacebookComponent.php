<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class FacebookComponent extends Component
{
    /**
     *  Facebook Object
     *
     * @var type Object
     */
    public $Facebook = null;

    /**
     *  Facebook Helper
     *
     * @var type Object
     */
    public $FacebookHelper = null;

    /**
     *  Facebook Access Token
     *
     * @var type Object
     */
    public $FacebookAccessToken = null;

    /**
     * Initialize Controllers, User Model and Session
     *
     * @param array $config
     */
    public function initialize(array $config)
    {
        /**
         * Get current controller
         */
        $this->Controller = $this->_registry->getController();
        //debug($this->Controller->request);

        $this->Controller->request->session()->start();

        /**
         * Attach Facebook Object
         */
        $this->Facebook = new Facebook([
            'app_id' => Configure::read('Facebook.app_id'),
            'app_secret' => Configure::read('Facebook.app_secret'),
            'default_graph_version' => 'v2.4',
        ]);

        /**
         * Attach Facebook Object Helper
         */
        $this->FacebookHelper = $this->Facebook->getRedirectLoginHelper();
    }

    /**
     * Create a Facebook URL Login
     *
     * @return link
     */
    public function urlLogin()
    {
        $url = $this->FacebookHelper->getLoginUrl(Router::url(Configure::read('Facebook.callback_login'), true), Configure::read('Facebook.scope'));
        $this->Controller->set('fb_url_login', $url);
        return $url;
    }

    /**
     * Facebook Get Access
     *
     * @return link
     */
    public function getAccess()
    {
        try {
            $accessToken = $this->FacebookHelper->getAccessToken();
        } catch(FacebookResponseException $e) {
            $this->Controller->Flash->error(__('Graph returned an error: ' . $e->getMessage()));
            return false;
        } catch(FacebookSDKException $e) {
            $this->Controller->Flash->error(__('Facebook SDK returned an error: ' . $e->getMessage()));
            return false;
        }

        if (! isset($accessToken)) {
            if ($this->FacebookHelper->getError()) {
                $msg  = "Error: " . $this->FacebookHelper->getError() . "<br>";
                $msg .= "Error Code: " . $this->FacebookHelper->getErrorCode() . "<br>";
                $msg .= "Error Reason: " . $this->FacebookHelper->getErrorReason() . "<br>";
                $msg .= "Error Description: " . $this->FacebookHelper->getErrorDescription() . "<br>";
                $this->Controller->Flash->error(__('Facebook SDK returned an error: ' . $msg));
                return false;
            } else {
                $this->Controller->Flash->error(__('Bad request'));
                return false;
            }
            $this->Controller->Flash->error(__('Bad request'));
            return false;
        }

        $this->FacebookAccessToken = $accessToken->getValue();
        return true;
    }

    /**
     * Facebook Get User Data
     */
    public function getUser()
    {
        try {

            $response = $this->Facebook->get('/me?fields=id,name,email', $this->FacebookAccessToken);
            $user = $response->getDecodedBody();
            $user['oauth_uid'] = $user['id'];
            $user['oauth_provider'] = 'facebook';
            $user['oauth_token'] = $this->FacebookAccessToken;
            $user['picture'] = 'http://graph.facebook.com/'.$user['oauth_uid'].'/picture?height=200&width=200';
            unset($user['id']);
            return $user;

        } catch(FacebookResponseException $e) {
            $this->Controller->Flash->error(__('Graph returned an error: ' . $e->getMessage()));
            return false;
        } catch(FacebookSDKException $e) {
            $this->Controller->Flash->error(__('Facebook SDK returned an error: ' . $e->getMessage()));
            return false;
        }
        $this->Controller->Flash->error(__('Bad request'));
        return false;
    }
}