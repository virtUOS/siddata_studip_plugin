<?php

/**
 * Class FaqController
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 */
class FaqController extends SiddataControllerAbstract
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/siddata/faq');

        PageLayout::setTitle("Siddata: FAQ");

        // Helpbar entry
        Helpbar::Get()->addLink("Häufig gestellte Fragen zu Siddata", $this->link_for('faq/index'));

    }

    /**
     * standard route
     */
    function index_action()
    {
        $links_widget = new LinksWidget();
        $links_widget->setTitle('Direkt zu:');
        $links_widget->addCSSClass('siddata-view');

        $this->sectionAnchors = [ 'siddata-faq-basic'         => 'Fragen zu Grundbegriffen',
                            'siddata-faq-usage'         => 'Fragen zur Verwendung',
                            'siddata-faq-data-privacy'  => 'Fragen zum Datenschutz'];
        foreach ($this->sectionAnchors as $anchor => $anchorText) {
            $links_widget->addLinkFromHTML('<a href="#'.$anchor.'">'.$anchorText.'</a>');
        }
        Sidebar::Get()->addWidget($links_widget);
    }
}
