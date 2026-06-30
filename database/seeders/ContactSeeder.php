<?php

namespace Database\Seeders;

use App\Domain\Contact\Models\Contact;
use Illuminate\Database\Seeder;

final class ContactSeeder extends Seeder
{
    public function run(): void
    {
        Contact::updateOrCreate(
            ['id' => 1],
            [
                'address' => 'г. Калуга, ул. Никитина, д. 41 стр. 1',
                'address2' => '248002, 3 этаж, офисы 313, 314',
                'phone' => '+7 (4842) 59-65-75',
                'email' => 'info@uniqset.com',
                'work_schedule' => 'Пн - Пт: с 9.00 до 18.00',
                'work_schedule2' => 'Сб, Вск - выходные',
                'latitude' => 54.505882234159415,
                'longitude' => 36.27238477976226,
                'telegram' => 'https://t.me/uniqset_gen',
                'vk' => 'https://vk.com/uniqset',
                'max' => '',
                'inn' => '4027139409',
                'ogrn' => '1194027002861',
            ],
        );
    }
}
