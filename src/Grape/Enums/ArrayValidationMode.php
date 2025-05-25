<?php

namespace Potager\Grape\Enums;

enum ArrayValidationMode
{
    case FailFast;
    case CollectErrors;
    case DropInvalid;
}