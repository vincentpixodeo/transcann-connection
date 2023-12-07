<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Contracts;

interface ObjectDataInterface
{
    /**
     * set Data
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static;

    /**
     * merge Data
     * @param array $data
     * @return $this
     */
    public function addData(array $data): static;

    /**
     * get Data
     * @param string|null $key
     * @param $default
     * @return mixed
     */
    public function getData(string $key = null, $default = null): mixed;
}