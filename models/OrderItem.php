<?php

class OrderItem {
    public int $id;
    public Product $product;
    public int $quantity;
    public float $priceAtPurchase;

    public function __construct(int $id, Product $product, int $quantity, float $priceAtPurchase) {
        $this->id = $id;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->priceAtPurchase = $priceAtPurchase;
    }
}