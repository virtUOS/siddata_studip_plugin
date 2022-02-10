<?php


$lookups = [
    'SiddataAnswerSearch'       => __DIR__ . '/lib/SiddataAnswerSearch.php',
    'SiddataCache'              => __DIR__ . '/lib/SiddataCache.php',
    'SiddataCrypt'              => __DIR__ . '/lib/SiddataCrypt.php',
    'SiddataDataManager'        => __DIR__ . '/lib/SiddataDataManager.php',
    'SiddataDebugLogger'        => __DIR__ . '/lib/SiddataDebugLogger.php',
    'SiddataRestClient'         => __DIR__ . '/lib/SiddataRestClient.php',
    'SiddataMap'                => __DIR__ . '/lib/SiddataMap.php',
    'SiddataActivity'           => __DIR__ . '/models/SiddataActivity.php',
    'SiddataGoal'               => __DIR__ . '/models/SiddataGoal.php',
    'SiddataRecommender'        => __DIR__ . '/models/SiddataRecommender.php',
    'SiddataStudent'            => __DIR__ . '/models/SiddataStudent.php',
    'SiddataPerson'             => __DIR__ . '/models/SiddataPerson.php',
    'SiddataControllerAbstract' => __DIR__ . '/controllers/SiddataController.php',
    'SiddataQuestion'           => __DIR__ . '/models/SiddataQuestion.php',
    'SiddataCourse'             => __DIR__ . '/models/SiddataCourse.php',
    'SiddataActivityComponent'  => __DIR__ . '/models/SiddataActivityComponent.php',
    'SiddataResource'           => __DIR__ . '/models/SiddataResource.php',
    'SiddataEvent'              => __DIR__ . '/models/SiddataEvent.php',
];

StudipAutoloader::addClassLookups($lookups);

/**
 * Class SiddataPlugin
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataPlugin extends StudIPPlugin implements SystemPlugin
{

    public $rest_client;
    public $debug = false;
    public $error_msg;
    public $data_manager;

    /**
     * SiddataPlugin constructor.
     */
    function __construct()
    {
        parent::__construct();

        // Configuration
        $config = Config::get();
        $this->debug = $config['SIDDATA_Debug_Info'];
        //        if (false === $this->debug) {
        //            // Nur E_ERROR- oder E_PARSE-Fehler melden
        //            error_reporting(E_ERROR|E_PARSE);
        //        }
        $this->error_msg = $config['SIDDATA_Error_Message'];

        $this->buildTopNavigation($config);

        $this->addProfileSettingsPage($config);

    }

    /**
     * Builds the top navigation
     * @param $config
     */
    private function buildTopNavigation($config)
    {
        // basic Top Navigation - see $this->perform()
        $main = new Navigation('Siddata');
        if ($config['SIDDATA_nav'] || UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_USER_NAV')) {
            $main->setImage(new Icon($this->getPluginURL() . '/assets/images/icons/blue/siddata.svg'), ['title' => 'Siddata']);
        }
        Navigation::addItem('/siddata', $main);

        $assistant = new Navigation('Studienassistent');
        $assistant->setURL(PluginEngine::getURL(
            $this,
            array(),
            'siddata/index'
        ));
        Navigation::addItem('/siddata/assistant', $assistant);

        $start = new Navigation('Start');
        $start->setURL(PluginEngine::getURL(
            $this,
            array(),
            'siddata/index'
        ));
        Navigation::addItem('/siddata/assistant/index', $start);

        $settings = new Navigation('Einstellungen');
        $settings->setURL(PluginEngine::getURL(
            $this,
            array(),
            'settings/index'
        ));
        Navigation::addItem('/siddata/settings', $settings);

        $faq = new Navigation('FAQ');
        $faq->setURL(PluginEngine::getURL(
            $this,
            array(),
            'faq/index'
        ));
        Navigation::addItem('/siddata/faq', $faq);
    }

    /**
     * Adds siddata profile settings in Stud.IP profile settings
     * @param $config
     */
    private function addProfileSettingsPage($config) {
        if (!$config['SIDDATA_nav'] && Navigation::hasItem('/profile/settings/')) {
            $profile_settings = new Navigation('Siddata-Studienassistent');
            $profile_settings->setURL(PluginEngine::getURL(
                $this,
                array(),
                'profile_settings/index'
            ));
            Navigation::addItem('/profile/settings/siddata', $profile_settings);
        }
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     *
     * @return void
     * @throws Trails_UnknownAction
     */
    public function perform($unconsumed_path)
    {
        // Configuration
        $config = Config::get();

        // Setup Rest Client
        $this->rest_client = SiddataRestClient::getInstance($config['SIDDATA_Brain_URL'],
            $config['SIDDATA_Proxy_URL'], $config['SIDDATA_Proxy_Port'], $config['SIDDATA_Debug_Info'], $config['SIDDATA_Error_Message'], $config['SIDDATA_api_key']);

        // Setup Data Manager
        $this->data_manager = new SiddataDataManager($this->rest_client);


        // add base stylesheets and javascritps
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/stylesheets/base.css?v=' . $this->getMetadata()['version']);
        PageLayout::addScript($this->getPluginURL() . '/assets/javascripts/base.js?v=' . $this->getMetadata()['version']);

        // build sidebar only considering Stud.IP 4.x
        $major_version = (int)substr($GLOBALS['SOFTWARE_VERSION'], 0, 1);
        $minor_version = (int)substr($GLOBALS['SOFTWARE_VERSION'], 2, 1);
        if ($major_version < 5 and $minor_version < 5) {
            $sidebar_img = $minor_version > 3 ? 'siddata-sidebar-logo-right.png': 'siddata-sidebar-logo-left.png';
            Sidebar::Get()->setImage($this->getPluginURL().'/assets/images/' . $sidebar_img);
        }
        Sidebar::Get()->setContextAvatar(Avatar::getAvatar(User::findCurrent()->getId()));

        parent::perform($unconsumed_path);
    }

    /**
     * Generates a SIDDATA-specific error message for users to see
     * @param string $msg
     */
    public function postError($msg="Es ist ein Fehler aufgetreten.") {
        PageLayout::postError($msg . " " . $this->error_msg);
    }

    /**
     * Generates a SIDDATA-specific success message for users to see
     * @param string $msg
     */
    public function postSuccess($msg="Die Aktion war erfolgreich.") {
        PageLayout::postSuccess($msg);
    }

}

function htmlFormatReady($text) {
    return formatReady(Studip\Markup::markAsHtml($text));
}
