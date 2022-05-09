<?php

    require __DIR__ . '/vendor/autoload.php';

    use Longman\TelegramBot\TelegramLog;
    use Psr\Log\LogLevel;
    use QuizBot\QuizBot;
    use Longman\TelegramBot\Commands\SystemCommands\LintoCommand;


    Dotenv\Dotenv::createImmutable(__DIR__)->load();

    $mysql_credentials = [
        'host'     => getenv('MYSQL_HOST'),
        'user'     => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'database' => getenv('MYSQL_DB'),
    ];

    $logger = new SimpleLog\Logger(__DIR__.getenv('LOG'), 'telegram_bot', getenv('LOG_LEVEL'));
    TelegramLog::initialize($logger);
    $logger->info('Got a webhook call');

    set_error_handler(function($errno, $message, $file, $line) use ($logger) {
        $levels = [
            E_ERROR => LogLevel::EMERGENCY, E_WARNING => LogLevel::WARNING, E_NOTICE => LogLevel::WARNING,
            E_STRICT => LogLevel::WARNING
        ];
        $logger->log($levels[$errno], '(' . $file . ':' . $line . ') ' . $message);
        return true;
    }, E_ALL);





   try {
        $bot = new QuizBot( getenv('TOKEN'), getenv('BOTNAME'));
        $bot->enableAdmins([intval(getenv('ADMIN')), intval(getenv('SECOND_ADMIN'))]);
        $bot->enableMySql($mysql_credentials);
        $bot->addCommandsPath(__DIR__ . '/quizbot/commands/');
        try {
            $list = $bot->getCommandsList();
        }
        catch(\Throwable $e) {

        }

       if(php_sapi_name() === 'cli' && $argv[1] === 'stats') {
           require __DIR__ . "/quizbot/commands/LintoCommand.php";
           $lintoCommand = new LintoCommand($bot);
           $lintoCommand->setChatId(getenv('ADMIN'))->execute();
           die;
       }

       $bot->handle();
    } catch (\Throwable $e) {
        $logger->log(LogLevel::EMERGENCY, '(' . $e->getFile() . ':' . $e->getLine() . ') ' . $e->getMessage());
    }
