<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Category;
use Modules\Setting\Entities\Unit;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        foreach ([['Bungkus', 'Bks'], ['Toples', 'Tpl'], ['Pcs', 'Pcs'], ['Kilogram', 'Kg']] as [$name, $short]) {
            Unit::create([
                'name' => $name,
                'short_name' => $short,
                'operator' => '*',
                'operation_value' => 1
            ]);
        }
    }
}
