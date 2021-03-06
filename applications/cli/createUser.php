<?php

use Kev\TicTacToe\Module\User\Application\Create\CreateUserCommand;
use Kev\Types\ValueObject\Uuid;

require __DIR__ . '/app.php';

try {
    $id = $argv[1];
    $app->dispatch(new CreateUserCommand(Uuid::random(), $id));
    echo sprintf('Created user with <%s> id' . PHP_EOL, $id);
} catch (\Exception $e) {
    error_log($e->getMessage(), $e->getCode());
    exit(1);
}
