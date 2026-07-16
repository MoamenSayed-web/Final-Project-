<?php

class Product {
    public int $id;
    public string $name;
    public string $description;
    public float $price;
    public int $quantity;
    public ?Category $category = null;

    public function __construct(int $id, string $name, string $description, float $price, int $quantity, ?Category $category = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->category = $category;
    }
}