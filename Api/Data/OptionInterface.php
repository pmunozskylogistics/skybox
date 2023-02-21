<?php

namespace Skybox\Checkout\Api\Data;

/**
 * Product Interface
 * @package Skybox\Checkout\Api\Data
 */
interface OptionInterface {
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getIdValue();

    /**
     * @param mixed $idValue
     */
    public function setIdValue($idValue);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param mixed $label
     */
    public function setLabel($label);
}
