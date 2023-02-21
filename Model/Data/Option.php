<?php
/**
 * Copyright © 2017 SkyBox Checkout Inc. All rights reserved.
 */

namespace Skybox\Checkout\Model\Data;

class Option implements \Skybox\Checkout\Api\Data\OptionInterface {
    private $id;
    private $idValue;
    private $value;
    private $label;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getIdValue()
    {
        return $this->idValue;
    }

    /**
     * @param mixed $idValue
     */
    public function setIdValue($idValue) {
        $this->idValue = $idValue;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value){
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }
}

