<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Data;

use WMS\Contracts\AbstractObjectData;


/**
 * @property string _MetaId_
 * @property string Available
 * @property int PEU
 * @property int REU
 * @property int EQR
 * @property int Id
 * @property int ItemId
 * @property int MaxPickQty
 * @property int MinPickQty
 * @property string PreparationType
 * @property \WMS\Data\Address\Location[] Location
 */
class Picking extends AbstractObjectData
{

}