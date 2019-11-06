<?php

namespace Yosmy\Phone;

class Classification
{
    /**
     * @var bool
     */
    private $voip;

    /**
     * @param bool $voip
     */
    public function __construct(bool $voip)
    {
        $this->voip = $voip;
    }

    /**
     * @return bool
     */
    public function isVoip(): bool
    {
        return $this->voip;
    }
}