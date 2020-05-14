<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use DominicJoas\DjImagetools\Utility\Helper;

class SettingsController extends ActionController {
    public const TINY_KEY = "tinifyKey";
    public const WIDTH = "widthForAll";
    public const HEIGHT = "heightForAll";
    public const OVERWRITE = "overwrite";
    public const SAME_FOLDER = "sameFolder";
    public const UPLOAD_PATH = "uploadPath";


    public function listAction() {
        Helper::saveSettings("lastActionMenuItem", "Settings");

        $settings = array();
        $settings[self::TINY_KEY] = Helper::getSettings(self::TINY_KEY);
        $settings[self::WIDTH] = Helper::getSettings(self::WIDTH);
        $settings[self::HEIGHT] = Helper::getSettings(self::HEIGHT);
        $settings[self::OVERWRITE] = Helper::getSettings(self::OVERWRITE);
        $settings[self::SAME_FOLDER] = Helper::getSettings(self::SAME_FOLDER);
        $settings[self::UPLOAD_PATH] = Helper::getSettings(self::UPLOAD_PATH);

        $this->view->assign("settings", $settings);
        $this->view->assign('typo3Version', explode(".", TYPO3_version)[0]);
        return $this->view->render();
    }

    /**
     * @param array settings
     * @throws
     */
    public function updateAction(array $settings) {
        var_dump($settings);
        Helper::saveSettings(self::TINY_KEY, $settings[self::TINY_KEY]);
        Helper::saveSettings(self::WIDTH, $settings[self::WIDTH]);
        Helper::saveSettings(self::HEIGHT, $settings[self::HEIGHT]);
        if($settings[self::OVERWRITE]) {
            Helper::saveSettings(self::OVERWRITE, "1");
        } else {
            Helper::saveSettings(self::OVERWRITE, "0");
        }
        if($settings[self::SAME_FOLDER]) {
            Helper::saveSettings(self::SAME_FOLDER, "1");
        } else {
            Helper::saveSettings(self::SAME_FOLDER, "0");
        }
        Helper::saveSettings(self::UPLOAD_PATH, $settings[self::UPLOAD_PATH]);
        $this->addFlashMessage("", Helper::getLang("settings.saved"));
        $this->redirect('list');
    }
}