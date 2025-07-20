<?php

namespace GuepardoSys\Core;

interface MigrationInterface
{
    public function up();
    public function down();
} 