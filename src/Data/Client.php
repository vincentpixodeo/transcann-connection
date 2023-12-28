<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data;

use WMS\Xtent\Contracts\AbstractObjectData;


/**
 * @property string _MetaId_
 * @property string Available
 * @property boolean BlockedAccount
 * @property int WarehousePartyCategory values [1: Supplier, 2: Recipient, 3: Storer, 4: Carrier]
 * @property boolean CustomsManagement
 * @property boolean RemaindersManagementinReception
 * @property \WMS\Xtent\Data\Reception\ReceptionStatus ReceptionStatus
 * @property int Id
 * @property string Name
 * @property \WMS\Xtent\Data\Address\Office Office
 * @property \WMS\Xtent\Data\Address\City OperationCity
 * @property string OperationAddress1
 * @property string OperationCityName
 * @property string OperationCountryId
 * @property string OperationCityAll
 * @property string ShortName
 *
 */
class Client extends AbstractObjectData
{

}