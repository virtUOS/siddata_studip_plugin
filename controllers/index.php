<?php

/**
 * Class IndexController
 * @author Sebastian Osada <sebastian.osada@uni.osnabrueck.de>
 *
 */
class IndexController extends SiddataControllerAbstract
{
    /**
     * Standard route
     * @throws Trails_DoubleRenderError
     */
    function index_action()
    {
        $this->redirect('siddata/index');
    }
}
