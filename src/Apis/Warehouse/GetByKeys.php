<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\Warehouse;

use WMS\Xtent\Contracts\HasLoadByKeysFunction;

class GetByKeys extends GetList
{
    use HasLoadByKeysFunction;
}