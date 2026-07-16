<?php

class CartItem {
    public int $id;
    public Product $product;
    public int $quantity;

    public function __construct(int $id, Product $product, int $quantity) {
        $this->id = $id;
        $this->product = $product;
        $this->quantity = $quantity;
    }

    public function getTotalPrice(): float {
        return $this->product->price * $this->quantity;
    }
}