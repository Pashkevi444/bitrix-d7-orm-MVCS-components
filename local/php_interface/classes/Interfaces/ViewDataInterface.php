<?php

namespace Interfaces;

interface ViewDataInterface
{
    /**
     * Get page properties.
     *
     * @return array
     */
    public function getPageProperties(): array;

    /**
     * Set page properties.
     *
     * @param array $pageProperties
     * @return void
     */
    public function setPageProperties(array $pageProperties): void;

    /**
     * Set the result of data processing.
     *
     * @param array $result
     * @return void
     */
    public function setResult(array $result): void;

    /**
     * Get the result of data processing.
     *
     * @return array
     */
    public function getResult(): array;

    /**
     * Set parameters.
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void;

    /**
     * Get parameters.
     *
     * @return array
     */
    public function getParams(): array;
}
