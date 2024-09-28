<?php
namespace fmr\page;

/**
 * Interface IPage
 * all page classes should implement this interfaces
 */
interface IAbstractPage {

    /**
     * reads the given requested parameter
     */
    public function readParameters();

    /**
     * checks the permissions of site
     */
    public function checkPermissions();

    /**
     * Reads/Get the data to be displayed on this page
     *
     */
    public function readData();

    /**
     * assign variables to the smarty template engine
     */
    public function assignVariables();

    /**
     * show the requested page*/
    public function show();
}