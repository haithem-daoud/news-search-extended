<?php
defined('TYPO3_MODE') || die();

call_user_func(
    function()
    {
        /**************************
        ** Extend News Extension **
        ***************************/
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
            // Extend News Model
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'news_search';
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/Dto/Search'][] = 'news_search';

            // Extend News Repository
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['GeorgRinger\\News\\Domain\\Repository\\NewsRepository'] = array(
                'className' => 'T3hd\\NewsSearch\\Domain\\Repository\\NewsRepository'
            );

            // Extend News Controller
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['GeorgRinger\\News\\Controller\\NewsController'] = array(
                'className' => 'T3hd\\NewsSearch\\Controller\\NewsController'
            );
        }
    }
);