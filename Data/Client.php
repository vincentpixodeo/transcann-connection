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
 * @property boolean BlockedAccount
 * @property boolean CustomsManagement
 * @property boolean RemaindersManagementinReception
 * @property Reception\ReceptionStatus ReceptionStatus
 * @property int Id
 * @property string Name
 * @property Address\Office Office
 * @property Address\City OperationCity
 * @property string OperationCityName
 * @property string ShortName
 */
class Client extends AbstractObjectData
{

}