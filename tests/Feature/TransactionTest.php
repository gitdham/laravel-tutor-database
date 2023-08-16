<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();
    DB::delete("DELETE FROM categories");
  }

  public function test_transaction_success() {
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

    DB::transaction(function () use ($data) {
      DB::insert("INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
      DB::insert("INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[1]);
    });

    $result = DB::select("SELECT * FROM categories");
    $this->assertCount(2, $result);
  }

  public function test_transaction_failed() {
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

    try {
      DB::transaction(function () use ($data) {
        DB::insert("INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
        DB::insert("INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
      });
    } catch (QueryException $error) {
      // expected
    }

    $result = DB::select("SELECT * FROM categories");
    $this->assertCount(0, $result);
  }

  public function test_manual_transaction_success() {
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

    try {
      DB::beginTransaction();
      DB::insert("INSERT INTO categories (id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
      DB::insert("INSERT INTO categories (id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[1]);
      DB::commit();
    } catch (QueryException $error) {
      DB::rollBack();
    }

    $result = DB::select("SELECT * FROM categories");
    $this->assertEquals(2, count($result));
  }

  public function test_manual_transaction_failed() {
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

    try {
      DB::beginTransaction();
      DB::insert("INSERT INTO categories (id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
      DB::insert("INSERT INTO categories (id, name, description, created_at) values (:id, :name, :description, :created_at)", $data[0]);
      DB::commit();
    } catch (QueryException $error) {
      DB::rollBack();
    }

    $result = DB::select("SELECT * FROM categories");
    $this->assertEquals(0, count($result));
  }
}
