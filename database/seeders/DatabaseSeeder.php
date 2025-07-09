<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use App\Models\Book;
use App\Models\BookClass;
use App\Models\BookDaerah;
use App\Models\Order;
use App\Models\OrderBook;
use App\Models\OrderDetail;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $regions = ['Demak', 'Jepara', 'Kudus'];
        $books =  ['Pendidikan pancasila', 'Bahasa Indonesia', 'Matematika','IPAS', 'Seni Budaya', 'Bahasa Inggris', 'Bahasa Jawa', 'Seni Rupa', 'Seni Tari', 'Seni Teater', 'Seni Musik', 'TIK', 'PJOK'];
        
        foreach ($books as $book){
            $newBook = Book::factory()->create([
                'name' => $book
            ]);
                for ($i = 1; $i <= 6; $i++) {
                    if ($newBook->name === 'IPAS' && $i <= 2) {
                        continue;
                    }
                
                    BookClass::factory()->create([
                        'class' => $i,
                        'book_id' => $newBook->id,
                    ]);
                }
        }

        foreach ($regions as $daerah){
            foreach ($books as $key => $book){
                BookDaerah::factory()->create([
                    'book_id' => $key+1,
                    'daerah' => $daerah
                ]);
            }
        }

        Order::factory(50)->create();
        OrderBook::factory(100)->create();
    }
}
