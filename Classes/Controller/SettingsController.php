<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use DominicJoas\DjImagetools\Utility\Helper;

class SettingsController extends ActionController {

    public function listAction() {
        Helper::saveSettings("lastPath", $GLOBALS["_GET"]["id"]);
        Helper::saveSettings("lastActionMenuItem", "Settings");

        $settings = array();
        $settings["tinifyKey"] = Helper::getSettings("tinifyKey");

        $this->view->assign("settings", $settings);
        return $this->view->render();
    }

    /**
     * @param array settings
     * @throws
     */
    public function updateAction(array $settings) {
        Helper::saveSettings("tinifyKey", $settings["tinifyKey"]);
        $this->redirect('list');
    }
}