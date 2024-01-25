<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Enums;

enum OrderPickedStatus: string
{
    case None = "R";
    case Partial = "J";
    case Full = "V";
}
