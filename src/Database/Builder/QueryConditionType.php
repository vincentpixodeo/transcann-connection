<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Database\Builder;

enum QueryConditionType: string
{
    case AND = 'AND';
    case OR = 'OR';
}
