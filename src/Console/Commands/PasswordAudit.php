<?php

namespace JordJD\LaravelPasswordSecurityAudit\Console\Commands;

use JordJD\CliProgressBar\ProgressBar;
use JordJD\LaravelPasswordSecurityAudit\Objects\CrackedUser;
use JordJD\PasswordCracker\Crackers\DictionaryCracker;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class PasswordAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:password-audit {--user-model=\\App\\User} {--password-field=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audits the security of your user\'s passwords';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $userModelClass = $this->option('user-model');
        $passwordField = $this->option('password-field');

        if (!class_exists($userModelClass)) {
            $this->error('Specified user model is not a valid class.');
            exit;
        }

        $userModel = new $userModelClass();

        if (!$userModel instanceof Model) {
            $this->error('Specified user model is not a Eloquent model.');
            exit;
        }

        $query = $userModel->query()
            ->select([$userModel->getKeyName(), 'password']);

        $numUsers = $query->count();

        if ($numUsers <= 0) {
            $this->error('No users found.');
            exit;
        }

        $cracker = new DictionaryCracker();
        $numPasswords = $cracker->getPasswordCount();

        $crackedUsers = collect();

        $progressBar = new ProgressBar();
        $progressBar->setMaxProgress($numUsers * $numPasswords);
        $progressBar->display();

        $userIndex = 0;

        $query->chunk(1000, function ($users) use ($numPasswords, $crackedUsers, $progressBar, $userIndex, $cracker, $passwordField) {
            /** @var Model $user */
            foreach ($users as $user) {
                $progressBar
                    ->setMessage("User ".$user->getKey())
                    ->setProgress($userIndex * $numPasswords)
                    ->display();

                $userIndex++;
                $hash = $user->$passwordField;

                $password = $cracker->crack($hash, function() use ($progressBar) {
                    $progressBar->advance()->display();
                });

                if ($password !== null) {
                    $crackedUsers->push(
                        new CrackedUser($user->getKey(), $password, $hash)
                    );
                }
            }

        });

        $progressBar->complete();

        $crackedUsersCount = $crackedUsers->count();

        $this->line($crackedUsersCount.' user password(s) were found to be weak.');

        if ($crackedUsersCount > 0) {
            $this->table([
                'Key ('.$userModel->getKeyName().')',
                'Password',
                'Hash'
            ], $crackedUsers->toArray());
        }

    }
}