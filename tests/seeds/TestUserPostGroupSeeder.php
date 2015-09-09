<?php

use Illuminate\Database\Seeder;

class TestUserPostGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('test_users') -> truncate();
        \DB::table('test_user_groups') -> truncate();
        \DB::table('test_groups') -> truncate();
        \DB::table('test_posts') -> truncate();

        $faker = Faker\Factory::create();
        //create 20 users, 10 below 20-30 yo, the rest >50. Half of the 20-30 yos are male.
        $user_seeds = [];
        for($i = 0; $i < 20; $i++ )
        {
            if( $i < 10 )
            {
                $dob = $faker -> dateTimeBetween('-30 years','-20 years') -> format('Y-m-d H:i:s');
                $gender = ( $i % 2 == 0 ) ? 1 : 0;
            } else {
                $gender = 1;
                $dob = $faker -> dateTimeBetween('-50 years','-31 years') -> format('Y-m-d H:i:s');
            }

            $user_seeds[]= [
                'email' => $faker -> email,
                'dob' => $dob,
                'gender' => $gender,
                'name' => $faker -> name];
        }

        \DB::table('test_users') -> insert($user_seeds);

        //create 2 posts for each user. half of them from one week ago, half of them one month ago
        $users = DB::table('test_users') -> get();
        foreach( $users as $user )
        {
            \DB::table('test_posts') -> insert([
                [
                    'user_id' => $user -> id,
                    'title' => $faker -> sentence,
                    'created_at' => $faker -> dateTimeBetween('-7 days','-1 day') -> format('Y-m-d H:i:s')
                ],
                [   'user_id' => $user -> id,
                    'title' => $faker -> sentence,
                    'created_at' => $faker -> dateTimeBetween('-12 days','-8 day') -> format('Y-m-d H:i:s')]
            ]);
        }

        //create 3 groups
        $group_seeds = [];
        for($i = 0; $i < 3; $i++ )
            $group_seeds[]= ['name' => $faker -> company];
        \DB::table('test_groups') -> insert($group_seeds);

        $groups = DB::table('test_groups') -> get();
        //assign 10 users in one group and 5 to the rest
        for( $i = 0; $i < count($users); $i++ ){

            if( $i < 10 )
                $group_id = $groups[0] -> id;
            else if( $i < 15 )
                $group_id = $groups[1] -> id;
            else $group_id = $groups[2] -> id;

            \DB::table('test_user_groups') -> insert([
                'user_id' => $users[$i] -> id,
                'group_id' => $group_id
                ]);
        }

    }
}
