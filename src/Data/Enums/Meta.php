<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Data\Enums;

enum Meta: string
{
    case Item = "836550aa-39cb-4d65-88f7-f784983020d4";
    case Client = "2995a231-373e-4b86-8ece-2a077d58d850";
    case Supplier = "8af7f198-e57a-4472-9a9a-a655e1f3f2d0";
    case Support = "6ebb817e-4c6b-4a7c-a907-1d3226f55bab";
    case Unit = "af55893b-82f2-40f1-b312-c8bf0d6e51bc";
    case Currency = "bebb590a-a6d8-448d-8278-398d4a04cc95";
    case Family = "052d4be8-f542-4795-a54d-37c5edfa6a1e";
    case ItemGencod = "d15c5f48-e090-47ce-90a0-4b538987701e";
    case PriorityRack = "48cba2dc-83cb-4d9c-8819-c8863656c8f0";
    case Warehouse = "45f8994a-54e6-427d-b15a-4da2a0832a40";
    case Preparation = "da2ac786-c537-4729-9bc1-ba26b1deaa21";
    case StorageMovement = "56e2fb58-9cf3-404a-8980-f16e355c94d3";
    case Reception = "6282fb42-6ff3-4b35-81ef-48dcbb193567";
    case ReceptionDetails = "e56dcb64-7194-4dd6-bbfb-7a410940c28f";
    case ItemReceived = "b026524b-035e-4f56-b57e-e76bf2cf07b7";
}
