<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Data\Reception;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;

/**
 * dolibarr data 'fourn/class/fournisseur.commande.class.php'
 */
class PurchaseOrder extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface
{
    use ConvertTranscanTrait;

    function getMapAttributes(): array
    {
        return [];
    }

    function getTranscanInstance(): string|ObjectDataInterface
    {
        return new Reception();
    }
}