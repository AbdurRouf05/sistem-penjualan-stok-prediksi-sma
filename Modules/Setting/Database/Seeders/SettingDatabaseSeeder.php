<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Setting\Entities\Setting;

class SettingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'company_name' => 'Toko Aneka Camilan Dua Putra',
            'company_email' => 'duaputra.camilan@gmail.com',
            'company_phone' => '0812-3456-7890',
            'notification_email' => 'duaputra.camilan@gmail.com',
            'default_currency_id' => 1,
            'default_currency_position' => 'prefix',
            'footer_text' => 'Toko Aneka Camilan Dua Putra &copy; ' . date('Y'),
            'company_address' => 'Jl. Contoh No. 1, Indonesia'
        ]);
    }
}
