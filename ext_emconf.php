<?php

$EM_CONF["dj_imagetools"] = array (
    'title' => 'ImageTools',
    'description' => 'An extension to compress, resize and update Meta-Data of images!',
    'category' => 'plugin',
    'author' => 'Dominic Joas',
    'author_company' => '',
    'author_email' => 'developing@domjos.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '11.5.0',
    'constraints' => array (
        'depends' => array ('typo3' => '8.7.0-11.5.99',),
        'conflicts' => array (),
        'suggests' => array (),
    ),
    'autoload' => array (
        'psr-4' => array ('DominicJoas\\DjImagetools\\' => 'Classes',),
    ),
    'uploadfolder' => true,
    'createDirs' => NULL,
    'clearcacheonload' => true,
);

