<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class NextCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'next';

    /**
     * @var string
     */
    protected $description = 'Show next puzzle';

    /**
     * @var string
     */
    protected $usage = '/next';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute()
    {
        return $this->telegram->executeCommand('quiz');
    }
}
