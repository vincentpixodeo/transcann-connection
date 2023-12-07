<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Data;

use WMS\Contracts\AbstractObjectData;
use WMS\Data\Address\City;
use WMS\Data\Address\Office;
use WMS\Data\Reception\ReceptionStatus as ReceptionStatus;


/**
 * @property string _MetaId_
 * @property string Available
 * @property boolean BlockedAccount
 * @property boolean CustomsManagement
 * @property boolean RemaindersManagementinReception
 * @property ReceptionStatus ReceptionStatus
 * @property int Id
 * @property string Name
 * @property Office Office
 * @property City OperationCity
 * @property string OperationCityName
 * @property string ShortName
 */
class Client extends AbstractObjectData
{

}