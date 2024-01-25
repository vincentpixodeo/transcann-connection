<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Database\Builder;

enum QueryCompareOperator: string
{

    case NotEqual = '<>';
    case NotEqualOther = '!=';
    case Equal = '=';
    case LessOrEqual = '<=';
    case GreaterOrEqual = '>=';
    case Less = '<';
    case Greater = '>';
    case Like = 'LIKE';
    case NotLike = 'NOT LIKE';
    case In = 'IN';
    case NotIn = 'NOT IN';
    case Between = 'BETWEEN';
    case NotBetween = 'NOT BETWEEN';
    case IsNull = 'IS NULL';
    case IsNotNull = 'IS NOT NULL';
}
