<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Enums;

enum ActionStatus: int
{
    case Init = 0;
    case Processing = 1;
    case Processed = 2;
}
