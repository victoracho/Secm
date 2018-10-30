<?php

namespace Fgimenez\UsuarioBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FgimenezUsuarioBundle extends Bundle {

    public function getParent() {
        return 'FOSUserBundle';
    }

}
