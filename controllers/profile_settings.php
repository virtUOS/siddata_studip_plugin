<?php

/**
 * Class ProfileSettingsController
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 *
 * This controller is used for the Siddata settings page which is displayed
 * at the profile settings page
 */
class ProfileSettingsController extends SiddataControllerAbstract
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->factory = new Flexi_TemplateFactory($this->plugin->getPluginPath() . '/templates');
    }

    /**
     * standard route
     */
    function index_action()
    {
        // Layout config
        PageLayout::setTitle('Siddata Einstellungen');
        if (Navigation::hasItem('/profile/settings/siddata')) {
            Navigation::activateItem('/profile/settings/siddata');
        }

        $this->main_nav_enabled = UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_USER_NAV');
    }

    /**
     * stores the submitted profile settings
     * @throws Trails_DoubleRenderError
     */
    function store_action() {
        $main_nav = Request::int('main_nav_enabled');

        UserConfig::get(User::findCurrent()->id)->store('SIDDATA_USER_NAV', $main_nav);

        $this->redirect('profile_settings/index');
    }
}
