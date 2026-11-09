<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Regenerates the cached user leaderboards so that visitors to /leaderboard don't have to wait for them
 *
 * Intended to be run daily by a cron job.
 */
class CacheLeaderboardsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Fetch the user leaderboards and save them to the cache.');

        return $parser;
    }

    /**
     * Fetches and caches the user leaderboards
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        if (!$usersTable->refreshLeaderboards()) {
            $io->err('The user leaderboards could not be written to the cache.');

            return static::CODE_ERROR;
        }

        $io->success('User leaderboards cached.');

        return static::CODE_SUCCESS;
    }
}
