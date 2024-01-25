<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Database\Builder;

enum QueryJoinType: string
{
    case InnerJoin = "INNER JOIN";
    case LeftJoin = "LEFT JOIN";
    case RightJoin = "RIGHT JOIN";
    case OnlyJoin = "JOIN";
}
