<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Data;

use WMS\Contracts\AbstractObjectData;
use WMS\Data\Address\City;
use WMS\Data\Address\Country;


/**
 * @property string _MetaId_
 * @property string Available
 * @property boolean BlockedAccount
 * @property string Email
 * @property int Id
 * @property string ManagerName
 * @property string ManagerPhoneNumber
 * @property string Name
 * @property string Office
 * @property string OperationAddress1
 * @property string OperationAddress2
 * @property string OperationAddress3
 * @property string OperationAddress4
 * @property string OperationCityName
 * @property string OperationZipCode
 * @property City OperationCity
 * @property Country OperationCountry
 */
class Consignee extends AbstractObjectData
{

}