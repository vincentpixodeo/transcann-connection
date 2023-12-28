<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Enums;

enum WarehousePartyCategory: int
{
    case Supplier = 1;
    case Recipient = 2;
    case Store = 3;
    case Carrier = 4;
}