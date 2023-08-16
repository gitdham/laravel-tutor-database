<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RowQueryTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();
    DB::delete("DELETE FROM categories");
  }

  public function test_crud() {
    $data = [
      'GADGET',
      'Gadget',
      'Gadget Category',
      '2023-07-31 10:10:10'
    ];

    DB::insert("INSERT INTO categories(id, name, description, created_at) values (?, ?, ?, ?)", $data);

    $result = DB::select("SELECT * FROM categories WHERE id=?", ['GADGET']);

    $this->assertCount(1, $result);
    $this->assertEquals('GADGET', $result[0]->id);
    $this->assertEquals('Gadget', $result[0]->name);
    $this->assertEquals('Gadget Category', $result[0]->description);
    $this->assertEquals('2023-07-31 10:10:10', $result[0]->created_at);
  }

  public function test_named_crud() {
    $data = [
      'id' => 'GADGET',
      'name' => 'Gadget',
      'description' => 'Gadget Category',
      'created_at' => '2023-07-31 10:10:10'
    ];

    DB::insert("INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)", $data);

    $result = DB::select("SELECT * FROM categories WHERE id=?", ['GADGET']);

    $this->assertCount(1, $result);
    $this->assertEquals('GADGET', $result[0]->id);
    $this->assertEquals('Gadget', $result[0]->name);
    $this->assertEquals('Gadget Category', $result[0]->description);
    $this->assertEquals('2023-07-31 10:10:10', $result[0]->created_at);
  }
}
