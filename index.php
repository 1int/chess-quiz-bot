<?php

    require __DIR__ . '/vendor/autoload.php';

    use Longman\TelegramBot\Exception\TelegramException;
    use Longman\TelegramBot\TelegramLog;
    use Psr\Log\LogLevel;
    use QuizBot\QuizBot;

    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    $bot_api_key  = getenv('TOKEN');
    $bot_username = getenv('BOTNAME');

    $mysql_credentials = [
        'host'     => getenv('MYSQL_HOST'),
        'user'     => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'database' => getenv('MYSQL_DB'),
    ];

    $logger = new SimpleLog\Logger(__DIR__.getenv('LOG'), 'telegram_bot', \Psr\Log\LogLevel::NOTICE);
    TelegramLog::initialize($logger);
    $logger->log(LogLevel::NOTICE, 'Got a webhook call');

    try {
        $bot = new QuizBot($bot_api_key, $bot_username);
        $bot->enableAdmins([intval(getenv('ADMIN')), intval(getenv('SECOND_ADMIN'))]);
        $bot->enableMySql($mysql_credentials);
        $bot->addCommandsPath(__DIR__ . '/quizbot/commands/');
        $bot->handle();
    } catch (TelegramException $e) {
        $logger->log(LogLevel::EMERGENCY, $e->getMessage());
    }
