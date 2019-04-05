<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use DominicJoas\DjImagetools\Utility\Helper;

class SettingsController extends ActionController {

    public function listAction() {
        Helper::saveSettings("lastActionMenuItem", "Settings");

        $settings = array();
        $settings["tinifyKey"] = Helper::getSettings("tinifyKey");
        $settings["widthForAll"] = Helper::getSettings("widthForAll");
        $settings["heightForAll"] = Helper::getSettings("heightForAll");
        $settings["overwrite"] = Helper::getSettings("overwrite");
        $settings["sameFolder"] = Helper::getSettings("sameFolder");
        $settings["uploadPath"] = Helper::getSettings("uploadPath");

        $this->view->assign("settings", $settings);
        return $this->view->render();
    }

    /**
     * @param array settings
     * @throws
     */
    public function updateAction(array $settings) {
        Helper::saveSettings("tinifyKey", $settings["tinifyKey"]);
        Helper::saveSettings("widthForAll", $settings["widthForAll"]);
        Helper::saveSettings("heightForAll", $settings["heightForAll"]);
        if($settings["overwrite"]) {
            Helper::saveSettings("overwrite", "1");
        } else {
            Helper::saveSettings("overwrite", "0");
        }
        if($settings["sameFolder"]) {
            Helper::saveSettings("sameFolder", "1");
        } else {
            Helper::saveSettings("sameFolder", "0");
        }
        Helper::saveSettings("uploadPath", $settings["uploadPath"]);
        $this->addFlashMessage("", Helper::getLang("settings.saved"));
        $this->redirect('list');
    }
}