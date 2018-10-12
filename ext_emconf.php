<?php

$EM_CONF[$_EXTKEY] = array (
    'title' => 'IMGCompromizer',
    'description' => 'An extension to compress and cut images with tinyfy!',
    'category' => 'plugin',
    'author' => 'Dominic Joas',
    'author_company' => '',
    'author_email' => 'entwicklung@domjos.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '0.0.1',
    'constraints' => array (
        'depends' => array ('typo3' => '7.6.0-9.5.99',),
        'conflicts' => array (),
        'suggests' => array (),
    ),
    'autoload' => array (
        'psr-4' => array ('DominicJoas\\Imgcompromizer\\' => 'Classes',),
    ),
    'uploadfolder' => true,
    'createDirs' => NULL,
    'clearcacheonload' => true,
);

