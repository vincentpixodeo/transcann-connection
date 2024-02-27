<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Enums;

enum ActionResultStatus: int
{
    case Start = 0;
    case Success = 1;
    case Fail = 2;
}
