<?php

namespace Database\Seeders;

use App\Models\Juz;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Language;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        // $juzData = [
        //     ['name' => 'Juz 1', 'first_surah' => 'Al-Fatihah', 'last_surah' => 'Al-Baqarah'],
        //     ['name' => 'Juz 2', 'first_surah' => 'Al-Baqarah', 'last_surah' => 'Al-Baqarah'],
        //     ['name' => 'Juz 3', 'first_surah' => 'Al-Baqarah', 'last_surah' => 'Al-Imran'],
        //     ['name' => 'Juz 4', 'first_surah' => 'Al-Imran', 'last_surah' => 'An-Nisa'],
        //     ['name' => 'Juz 5', 'first_surah' => 'An-Nisa', 'last_surah' => 'An-Nisa'],
        //     ['name' => 'Juz 6', 'first_surah' => 'An-Nisa', 'last_surah' => 'Al-Anam'],
        //     ['name' => 'Juz 7', 'first_surah' => 'Al-Anam', 'last_surah' => 'Al-Araf'],
        //     ['name' => 'Juz 8', 'first_surah' => 'Al-Araf', 'last_surah' => 'Al-Anfal'],
        //     ['name' => 'Juz 9', 'first_surah' => 'Al-Anfal', 'last_surah' => 'At-Tawbah'],
        //     ['name' => 'Juz 10', 'first_surah' => 'At-Tawbah', 'last_surah' => 'Yunus'],
        //     ['name' => 'Juz 11', 'first_surah' => 'Yunus', 'last_surah' => 'Hud'],
        //     ['name' => 'Juz 12', 'first_surah' => 'Hud', 'last_surah' => 'Yusuf'],
        //     ['name' => 'Juz 13', 'first_surah' => 'Yusuf', 'last_surah' => 'Ibrahim'],
        //     ['name' => 'Juz 14', 'first_surah' => 'Ibrahim', 'last_surah' => 'Al-Hijr'],
        //     ['name' => 'Juz 15', 'first_surah' => 'Al-Hijr', 'last_surah' => 'An-Nahl'],
        //     ['name' => 'Juz 16', 'first_surah' => 'An-Nahl', 'last_surah' => 'Al-Isra'],
        //     ['name' => 'Juz 17', 'first_surah' => 'Al-Isra', 'last_surah' => 'Al-Kahf'],
        //     ['name' => 'Juz 18', 'first_surah' => 'Al-Kahf', 'last_surah' => 'Maryam'],
        //     ['name' => 'Juz 19', 'first_surah' => 'Maryam', 'last_surah' => 'Ta-Ha'],
        //     ['name' => 'Juz 20', 'first_surah' => 'Ta-Ha', 'last_surah' => 'Al-Anbiya'],
        //     ['name' => 'Juz 21', 'first_surah' => 'Al-Anbiya', 'last_surah' => 'Al-Hajj'],
        //     ['name' => 'Juz 22', 'first_surah' => 'Al-Hajj', 'last_surah' => 'Al-Muminun'],
        //     ['name' => 'Juz 23', 'first_surah' => 'Al-Muminun', 'last_surah' => 'An-Nur'],
        //     ['name' => 'Juz 24', 'first_surah' => 'An-Nur', 'last_surah' => 'Al-Furqan'],
        //     ['name' => 'Juz 25', 'first_surah' => 'Al-Furqan', 'last_surah' => 'Ash-Shuara'],
        //     ['name' => 'Juz 26', 'first_surah' => 'Ash-Shuara', 'last_surah' => 'Az-Zumar'],
        //     ['name' => 'Juz 27', 'first_surah' => 'Az-Zumar', 'last_surah' => 'Fussilat'],
        //     ['name' => 'Juz 28', 'first_surah' => 'Fussilat', 'last_surah' => 'Al-Jathiya'],
        //     ['name' => 'Juz 29', 'first_surah' => 'Al-Jathiya', 'last_surah' => 'Al-Mulk'],
        //     ['name' => 'Juz 30', 'first_surah' => 'Al-Mulk', 'last_surah' => 'An-Nas'],
        // ];

        // foreach ($juzData as $juz) {
        //     Juz::create($juz);
        // }

        $languages = [
            ['name' => 'Indonesia', 'edition' => 'id.indonesian', 'code' => 'id'],
            ['name' => 'English', 'edition' => 'en.sahih', 'code' => 'en'],
            ['name' => 'Malay', 'edition' => 'ms.basmeih', 'code' => 'ms'],
            ['name' => 'Arabic', 'edition' => 'ar.alafasy', 'code' => 'ar'],
        ];

        foreach ($languages as $lang) {
            Language::create($lang);
        }
    }
}
