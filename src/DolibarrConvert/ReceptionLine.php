<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByDatabaseTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

/**
 * @property int $rowid
 * @property int $fk_commande
 * @property int $fk_product
 * @property int $fk_commandefourndet
 * @property int $fk_projet
 * @property int $fk_reception
 * @property float $qty
 * @property int $fk_entrepot
 * @property int $fk_user
 * @property string $comment
 * @property string $batch
 * @property int $status
 * @property string $datec
 * @property string $eatby
 * @property string $sellby
 * @property float $cost_price
 */
class ReceptionLine extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    use CanSaveDataByDatabaseTrait;

    public function getMainTable(): string
    {
        return 'commande_fournisseur_dispatch';
    }

    public function getPrimaryKey(): string
    {
        return 'rowid';
    }
}