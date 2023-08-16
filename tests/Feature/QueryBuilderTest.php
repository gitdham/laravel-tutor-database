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
}
