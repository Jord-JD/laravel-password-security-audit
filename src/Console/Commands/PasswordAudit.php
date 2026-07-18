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
    protected $signature = 'security:password-audit {--user-model=} {--password-field=password} {--show-secrets : Include recovered plaintext passwords and hashes in console output}';

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
        if (!$userModelClass) {
            $userModelClass = class_exists('App\\Models\\User') ? 'App\\Models\\User' : 'App\\User';
        }
        $passwordField = $this->option('password-field');

        if (!is_string($passwordField) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $passwordField)) {
            $this->error('Specified password field is not a valid column name.');

            return 1;
        }

        if (!class_exists($userModelClass)) {
            $this->error('Specified user model is not a valid class.');
            return 1;
        }

        $userModel = new $userModelClass();

        if (!$userModel instanceof Model) {
            $this->error('Specified user model is not a Eloquent model.');
            return 1;
        }

        $query = $userModel->query()
            ->select([$userModel->getKeyName(), $passwordField]);

        $numUsers = $query->count();

        if ($numUsers <= 0) {
            $this->error('No users found.');
            return 1;
        }

        $cracker = new DictionaryCracker();
        $numPasswords = $cracker->getPasswordCount();

        $crackedUsers = collect();

        $progressBar = new ProgressBar();
        $progressBar->setMaxProgress($numUsers * $numPasswords);
        $progressBar->display();

        $userIndex = 0;

        $query->chunk(1000, function ($users) use ($numPasswords, $crackedUsers, $progressBar, &$userIndex, $cracker, $passwordField) {
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

        if ($crackedUsersCount > 0 && $this->option('show-secrets')) {
            $this->warn('Recovered plaintext passwords and hashes are sensitive. Do not save or share this output.');
            $this->table(['Key ('.$userModel->getKeyName().')', 'Password', 'Hash'], $crackedUsers->toArray());
        } elseif ($crackedUsersCount > 0) {
            $this->table(
                ['Key ('.$userModel->getKeyName().')'],
                $crackedUsers->map(function (CrackedUser $user) {
                    return $user->toSafeArray();
                })->toArray()
            );
        }

        return 0;
    }
}
