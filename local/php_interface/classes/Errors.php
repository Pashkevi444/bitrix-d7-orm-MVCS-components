<?php

trait Errors
{

    /**
     * Sets the 404 error page and halts execution.
     *
     * This method triggers a 404 Not Found error, sets the appropriate HTTP header,
     * and redirects to the custom 404 error page. The execution is then stopped.
     *
     * @throws HttpException Throws an exception with 404 status.
     * @return never
     */
    public function set404(): never
    {
        global $APPLICATION;

        if (!defined("ERROR_404")) {
            define("ERROR_404", "Y");
        }

        \CHTTP::setStatus("404 Not Found");
        $APPLICATION->RestartWorkarea();
        require(\Bitrix\Main\Application::getDocumentRoot() . "/404.php");
        exit();
    }

}