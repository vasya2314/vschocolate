<?php

namespace App\Console\Commands;

use App\Actions\User\ChangeRoleUserAction;
use App\Actions\User\StoreUserAction;
use App\Actions\User\VerifyEmailUserAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin-user {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create super admin command';

    /**
     * Execute the console command.
     */
    public function handle(
        StoreUserAction $storeUserAction,
        VerifyEmailUserAction $verifyEmailUserAction,
        ChangeRoleUserAction $changeRoleUserAction
    ): void
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        try {
            DB::transaction(function () use ($storeUserAction, $email, $password, $verifyEmailUserAction, $changeRoleUserAction) {
                $user = $storeUserAction->handle([
                    'email' => $email,
                    'password' => $password,
                ]);

                $verifyEmailUserAction->handle($user, Carbon::now());
                $changeRoleUserAction->handle($user, User::ROLE_SUPER_ADMIN);
            });

            $this->info('Пользователь успешно создан');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
