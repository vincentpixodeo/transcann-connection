<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Enums;

enum ReceptionStatus: int
{
    case Waiting = 0;
    case Planned = 1;
    case Validated = 2;
    case Intermediate = 3;
    case Reserved = 4;
    case AtTheQuay = 5;
    case Deleted = 6;
    case RFReceptionInProgress = 7;
}
