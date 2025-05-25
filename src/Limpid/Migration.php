<?php

namespace Potager\Limpid;

abstract class Migration
{
    abstract public string $tableName;
    abstract public function up();
    abstract public function down();
}