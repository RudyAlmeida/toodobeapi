<?php

use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Administrador Toodobe',
            'email' => 'toodobe@toodobe.com',
            'password' => 'mYvhMXjbdM77qUQK',
            'role' => 'admin',
            'birthday' => '1988/06/07',
            'address_city' => 'São Paulo',
            'address_state' => 'SP',
            'address_country' => 'BR',
            'affiliate_code' => 'toodobe',
            'affiliate_type' => 'influenciador',
            'referred_code' => 'uvenis',
            'property_value' => 1000000.00,
            'first_installment_of_property' => 10600.66,
            'last_installment_of_property' => 2885.66,
            'expected_income' => 35335.53,
            'mobile' => 11988038876,
            'registry_code' => 36632008844,
            'email_verified_at' => \Carbon\Carbon::now()
        ]);

//        User::create([
//            'name' => 'Marco Antonio',
//            'email' => 'marantolove@hotmail.com',
//            'password' => '123456',
//            'role' => 'admin',
//            'birthday' => '1988/07/06',
//            'address_city' => 'São Paulo',
//            'address_state' => 'SP',
//            'address_country' => 'BR',
//            'affiliate_code' => 'toodobe1',
//            'affiliate_type' => 'influenciador',
//            'referred_code' => '',
//            'mobile' => 11988038876,
//            'registry_code' => '40206979061',
//            'email_verified_at' => \Carbon\Carbon::now()
//        ]);

//        User::create([
//            'name' => 'Will',
//            'email' => 'will.wfm@hotmail.com',
//            'password' => '123456',
//            'role' => 'admin',
//            'birthday' => '1988/07/06',
//            'address_city' => 'São Paulo',
//            'address_state' => 'SP',
//            'address_country' => 'BR',
//            'affiliate_code' => 'toodobe1',
//            'affiliate_type' => 'influenciador',
//            'referred_code' => 'toodobe2',
//            'property_value' => 1000000.00,
//            'first_installment_of_property' => 10600.66,
//            'last_installment_of_property' => 2885.66,
//            'expected_income' => 35335.53,
//            'mobile' => 14988203539,
//            'registry_code' => '29216265000',
//            'email_verified_at' => \Carbon\Carbon::now()
//        ]);

//        User::create([
//            'name' => 'Natan Fialho',
//            'email' => 'natan@toodobe.com ',
//            'password' => '123456',
//            'role' => 'admin',
//            'birthday' => '1988/07/06',
//            'address_city' => 'São Paulo',
//            'address_state' => 'SP',
//            'address_country' => 'BR',
//            'affiliate_code' => 'toodobe2',
//            'affiliate_type' => 'influenciador',
//            'referred_code' => '',
//            'mobile' => 11988038876,
//            'registry_code' => '08462834090',
//            'email_verified_at' => \Carbon\Carbon::now()
//        ]);
    }
}
