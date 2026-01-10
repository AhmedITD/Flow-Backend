<?php

// namespace App\Console\Commands;

// use App\Models\User;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\DB;

// class UpdatePhoneVerifiedAt extends Command
// {
//     /**
//      * The name and signature of the console command.
//      *
//      * @var string
//      */
//     protected $signature = 'users:update-phone-verified-at {--use-now : Use current timestamp instead of created_at}';

//     /**
//      * The console command description.
//      *
//      * @var string
//      */
//     protected $description = 'Update phone_verified_at for all users where it is NULL';

//     /**
//      * Execute the console command.
//      */
//     public function handle()
//     {
//         $this->info('Checking for users with NULL phone_verified_at...');
        
//         $count = User::whereNull('phone_verified_at')->count();
        
//         if ($count === 0) {
//             $this->info('No users found with NULL phone_verified_at.');
//             return Command::SUCCESS;
//         }
        
//         $this->info("Found {$count} user(s) with NULL phone_verified_at.");
        
//         if (!$this->confirm('Do you want to update them?', true)) {
//             $this->info('Operation cancelled.');
//             return Command::SUCCESS;
//         }
        
//         if ($this->option('use-now')) {
//             // Use current timestamp
//             $updated = DB::table('users')
//                 ->whereNull('phone_verified_at')
//                 ->update(['phone_verified_at' => now()]);
            
//             $this->info("Updated {$updated} user(s) with current timestamp.");
//         } else {
//             // Use created_at timestamp (maintains historical accuracy)
//             $updated = DB::statement("
//                 UPDATE users 
//                 SET phone_verified_at = created_at 
//                 WHERE phone_verified_at IS NULL
//             ");
            
//             if ($updated) {
//                 $this->info("Updated {$count} user(s) using their created_at timestamp.");
//             }
//         }
        
//         $this->info('Done!');
//         return Command::SUCCESS;
//     }
// }
