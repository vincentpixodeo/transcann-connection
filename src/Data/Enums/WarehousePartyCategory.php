<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Enums;

enum WarehousePartyCategory: int
{
    case Supplier = 0;
    case Recipient = 1;
    case Store = 2;
    case Carrier = 3;
}