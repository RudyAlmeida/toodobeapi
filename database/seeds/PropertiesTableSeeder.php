<?php

use App\Properties;
use Illuminate\Database\Seeder;

class PropertiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Properties::create([
            "property_value" => 1000000.00,
            "first_installment" => 10600.66,
            "last_installment" => 2885.66,
            "income_value" => 35335.53
        ]);

        Properties::create([
            "property_value" => 950000.00,
            "first_installment" => 10093.94,
            "last_installment" => 2742.62,
            "income_value" => 33646.47
        ]);

        Properties::create([
            "property_value" => 900000.00,
            "first_installment" => 9553.00,
            "last_installment" => 2599.59,
            "income_value" => 31843.33
        ]);

        Properties::create([
            "property_value" => 850000.00,
            "first_installment" => 9044.31,
            "last_installment" => 2456.56,
            "income_value" => 30147.70
        ]);

        Properties::create([
            "property_value" => 800000.00,
            "first_installment" => 8505.33,
            "last_installment" => 2313.53,
            "income_value" => 28351.10
        ]);

        Properties::create([
            "property_value" => 750000.00,
            "first_installment" => 7981.50,
            "last_installment" => 2170.49,
            "income_value" => 26605.00
        ]);

        Properties::create([
            "property_value" => 700000.00,
            "first_installment" => 7457.66,
            "last_installment" => 2027.46,
            "income_value" => 24858.87
        ]);

        Properties::create([
            "property_value" => 650000.00,
            "first_installment" => 6933.83,
            "last_installment" => 1884.43,
            "income_value" => 23112.77
        ]);

        Properties::create([
            "property_value" => 550000.00,
            "first_installment" => 5895.43,
            "last_installment" => 1598.36,
            "income_value" => 19651.43
        ]);

        Properties::create([
            "property_value" => 500000.00,
            "first_installment" => 5362.33,
            "last_installment" => 1455.33,
            "income_value" => 17874.43
        ]);

        Properties::create([
            "property_value" => 450000.00,
            "first_installment" => 4838.50,
            "last_installment" => 1312.30,
            "income_value" => 16128.33
        ]);

        Properties::create([
            "property_value" => 400000.00,
            "first_installment" => 4320.99,
            "last_installment" => 1169.27,
            "income_value" => 14403.30
        ]);

        Properties::create([
            "property_value" => 350000.00,
            "first_installment" => 3790.84,
            "last_installment" => 1026.24,
            "income_value" => 12636.13
        ]);

        Properties::create([
            "property_value" => 300000.00,
            "first_installment" => 3267.00,
            "last_installment" => 883.20,
            "income_value" => 10890.00
        ]);

        Properties::create([
            "property_value" => 250000.00,
            "first_installment" => 2766.59,
            "last_installment" => 740.44,
            "income_value" => 7397.77
        ]);

        Properties::create([
            "property_value" => 200000.00,
            "first_installment" => 2219.33,
            "last_installment" => 597.14,
            "income_value" => 7397.77
        ]);

        Properties::create([
            "property_value" => 150000.00,
            "first_installment" => 1695.50,
            "last_installment" => 454.10,
            "income_value" => 5651.67
        ]);

        Properties::create([
            "property_value" => 100000.00,
            "first_installment" => 1171.66,
            "last_installment" => 311.06,
            "income_value" => 3905.53
        ]);
    }
}
