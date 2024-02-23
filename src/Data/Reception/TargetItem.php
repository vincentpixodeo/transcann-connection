<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Reception;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;

/**
 * @property string _MetaId_
 * @property string Available
 * @property int BatchManagement
 * @property string Description
 * @property string ExternalReference
 * @property int Id
 * @property string ItemCode
 * @property string ListOfGencod
 * @property string StatusInbound
 * @property string StatusOutbound
 * @property string StatusReturn
 */
class TargetItem extends AbstractObjectData implements ObjectDataInterface
{

}