<?php


namespace test\mock\Ingenerator\KohanaExtras\Routing;

use Ingenerator\KohanaExtras\Routing\URLIdentifiableEntity;

class UrlIdentifiableStringStub implements URLIdentifiableEntity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getURLId(): string
    {
        return $this->id;
    }

}
