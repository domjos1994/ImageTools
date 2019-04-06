<?php

$EM_CONF[$_EXTKEY] = array (
    'title' => 'ImageTools',
    'description' => 'An extension to compress, resize and update Meta-Data of images!',
    'category' => 'plugin',
    'author' => 'Dominic Joas',
    'author_company' => '',
    'author_email' => 'developing@domjos.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '0.0.2',
    'constraints' => array (
        'depends' => array ('typo3' => '7.6.0-9.5.99',),
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

