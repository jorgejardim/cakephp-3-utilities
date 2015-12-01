<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Facebook\Facebook;

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
     * Initialize Controllers, User Model and Session
     *
     * @param array $config
     */
    public function initialize()
    {
        /**
         * Get current controller
         */
        $this->Controller = $this->_registry->getController();
        //debug($this->Controller->request);

        /**
         * Start session if not already started
         */
        if ($this->isSessionStarted() === FALSE)
        {
            $this->Controller->request->session()->start();
        }

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
}