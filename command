#!/usr/bin/env php
<?php

use App\CommandHandler;

include_once 'src/bootstrap.php';

$handler = new CommandHandler();

switch ($argv[1] ?? null) {
    case 'leads':
        try {
            $handler->addLeads($argv[2] ?? 0);
        } catch (Exception $e) {
            die((string)$e);
        }
        break;
    case 'multi-list':
        $handler->addMultiList();
        break;
    default: echo 'неизвестная команда';
}