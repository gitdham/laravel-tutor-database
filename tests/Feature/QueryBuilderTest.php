<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();
    DB::delete("DELETE FROM products");
    DB::delete("DELETE FROM categories");
  }

  private function insertCategories() {
    $categories = [
      [
        'id' => 'GADGET',
        'name' => 'Gadget',
        'description' => 'Gadget Category',
        'created_at' => '2023-07-30 08:10:10'
      ],
      [
        'id' => 'FOOD',
        'name' => 'Food',
        'created_at' => '2023-07-30 10:10:10'
      ],
      [
        'id' => 'SMARTPHONE',
        'name' => 'Smartphone',
        'description' => 'Smartphone Category',
        'created_at' => '2023-07-31 11:10:10'
      ],
      [
        'id' => 'LAPTOP',
        'name' => 'Laptop',
        'description' => 'Laptop Category',
        'created_at' => '2023-07-31 13:10:10'
      ],
    ];

    foreach ($categories as $category) {
      DB::table('categories')->insert($category);
    }
  }

  private function insertManyCategories() {
    for ($i = 0; $i < 100; $i++) {
      DB::table('categories')->insert([
        'id' => "CATEGORY-$i",
        'name' => "Category $i",
        'created_at' => '2023-07-17 23:03:00'
      ]);
    }
  }

  private function insertProducts() {
    $this->insertCategories();

    $products = [
      [
        'id' => '1',
        'name' => 'iPhone 14 Pro Max',
        'category_id' => 'SMARTPHONE',
        'price' => 13000000,
        'created_at' => '2023-08-16 12:40:23',
      ],
      [
        'id' => '2',
        'name' => 'Samsung Galaxy S21 Ultra',
        'category_id' => 'SMARTPHONE',
        'price' => 15000000,
        'created_at' => '2023-08-16 12:40:23',
      ],
      [
        'id' => '3',
        'name' => 'Bakso',
        'category_id' => 'FOOD',
        'price' => 20000,
        'created_at' => '2023-08-18 20:42:23',
      ],
      [
        'id' => '4',
        'name' => 'Mie Ayam',
        'category_id' => 'FOOD',
        'price' => 20000,
        'created_at' => '2023-08-16 20:42:23',
      ],
      [
        'id' => '5',
        'name' => 'Seblak',
        'category_id' => 'FOOD',
        'price' => 10000,
        'created_at' => '2023-08-16 20:56:23',
      ],
    ];

    foreach ($products as $product) {
      DB::table('products')->insert($product);
    }
  }

  public function test_insert() {
    $data = [
      [
        'id' => 'GADGET',
        'name' => 'Gadget',
        'description' => 'Gadget Category',
        'created_at' => '2023-07-31 10:10:10'
      ],
      [
        'id' => 'FOOD',
        'name' => 'Food',
        'description' => 'Food Category',
        'created_at' => '2023-07-31 10:10:10'
      ]
    ];

    DB::table('categories')->insert($data[0]);
    DB::table('categories')->insert($data[1]);

    $result = DB::select("SELECT COUNT(id) AS total FROM categories");
    $this->assertEquals(2, $result[0]->total);
  }

  public function test_select() {
    $this->insertCategories();
    $collection = DB::table('categories')->select(['id', 'name'])->get();
    $this->assertNotNull($collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_where() {
    $this->insertCategories();
    // SELECT * FROM categories WHERE (id='SMARTPHONE' OR id='LAPTOP')
    $collection = DB::table('categories')->where(function (QueryBuilder $builder) {
      $builder->where('id', '=', 'SMARTPHONE');
      $builder->orWhere('id', '=', 'LAPTOP');
    })->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_where_between() {
    $this->insertCategories();
    $collection = DB::table('categories')->whereBetween('created_at', ['2023-07-31 00:00:00', '2023-07-31 23:59:59'])->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_where_in() {
    $this->insertCategories();
    $collection = DB::table('categories')->whereIn('id', ['FOOD', 'GADGET'])->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_where_null() {
    $this->insertCategories();
    $collection = DB::table('categories')->whereNull('description')->get();

    $this->assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_where_date() {
    $this->insertCategories();
    $collection = DB::table('categories')->whereDate('created_at', '2023-07-31')->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_update_where() {
    $this->insertCategories();
    DB::table('categories')->where('id', '=', 'SMARTPHONE')
      ->update(['name' => 'Handphone']);

    $collection = DB::table('categories')->where('name', '=', 'Handphone')->get();

    $this->assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_update_or_insert() {
    $voucher = [
      'name' => 'Voucher',
      'description' => 'Ticket and Voucher',
      'created_at' => '2023-08-16 19:49:23',
    ];
    DB::table('categories')->updateOrInsert(['id' => 'VOUCHER'], $voucher);

    $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();

    $this->assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_increment() {
    DB::table('counters')->where('id', '=', 'sample')->increment('counter', 1);
    $collection = DB::table('counters')->where('id', '=', 'sample')->get();

    self::assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_delete() {
    $this->insertCategories();
    DB::table('categories')->where('id', '=', 'SMARTPHONE')->delete();

    $collection = DB::table('categories')->where('id', '=', 'SMARTPHONE')->get();
    $this->assertCount(0, $collection);
  }

  public function test_join() {
    $this->insertProducts();
    $collection = DB::table('products')
      ->join('categories', 'products.category_id', '=', 'categories.id')
      ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price')
      ->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_ordering() {
    $this->insertProducts();
    $collection = DB::table('products')
      ->orderByDesc('price')
      ->orderBy('name')
      ->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_take_skip() {
    $this->insertProducts();
    $collection = DB::table('categories')
      ->skip(2)
      ->take(2)
      ->get();

    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_chunk_result() {
    $this->insertProducts();
    DB::table('categories')
      ->orderBy('id')
      ->chunk(1, function ($categories) {
        $this->assertNotNull($categories);
        foreach ($categories as $category) {
          Log::info(json_encode($category));
        }
      });
  }

  public function test_lazy_result() {
    $this->insertManyCategories();
    $collection = DB::table('categories')->orderBy('id')->lazy(10)->take(4);
    $this->assertNotNull($collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_cursor_result() {
    $this->insertManyCategories();
    $collection = DB::table('categories')->orderBy('id')->cursor();
    $this->assertNotNull($collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_aggregate() {
    $this->insertProducts();

    $collection = DB::table('products')->count('id');
    $this->assertEquals(2, $collection);

    $collection = DB::table('products')->max('price');
    $this->assertEquals(15000000, $collection);
    Log::info(json_encode($collection));

    $collection = DB::table('products')->min('price');
    $this->assertEquals(13000000, $collection);
    Log::info(json_encode($collection));

    $collection = DB::table('products')->avg('price');
    $this->assertEquals(14000000, $collection);
    Log::info(json_encode($collection));

    $collection = DB::table('products')->sum('price');
    $this->assertEquals(28000000, $collection);
    Log::info(json_encode($collection));
  }

  public function test_raw_aggregate() {
    $this->insertProducts();

    $collection = DB::table('products')
      ->select(
        DB::raw('count(*) as total_product'),
        DB::raw('min(price) as min_price'),
        DB::raw('max(price) as max_price')
      )->get();

    $this->assertEquals(2, $collection[0]->total_product);
    $this->assertEquals(13000000, $collection[0]->min_price);
    $this->assertEquals(15000000, $collection[0]->max_price);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_grouping() {
    $this->insertProducts();

    $collection = DB::table('products')
      ->select('category_id', DB::raw('count(*) as total_product'))
      ->groupBy('category_id')
      ->orderByDesc('category_id')
      ->get();

    $this->assertCount(2, $collection);
    $this->assertEquals('SMARTPHONE', $collection[0]->category_id);
    $this->assertEquals('FOOD', $collection[1]->category_id);
    $this->assertEquals(2, $collection[0]->total_product);
    $this->assertEquals(2, $collection[1]->total_product);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_having() {
    $this->insertProducts();

    $collection = DB::table('products')
      ->select('category_id', DB::raw('count(*) as total_product'))
      ->groupBy('category_id')
      ->orderByDesc('category_id')
      ->having(DB::raw('count(*)'), '>', 2)
      ->get();

    $this->assertCount(1, $collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function test_locking() {
    $this->insertProducts();

    DB::transaction(function () {
      $collection = DB::table('products')
        ->where('id', '=', '1')
        ->lockForUpdate()
        ->get();

      $this->assertCount(1, $collection);

      $collection->each(function ($item) {
        Log::info(json_encode($item));
      });
    });
  }
}
