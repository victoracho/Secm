<?php

namespace Fgimenez\CrudBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FgimenezCrudBundle extends Bundle
{
    public function getParent() {
        return 'PetkoparaCrudGeneratorBundle';
    }
}
