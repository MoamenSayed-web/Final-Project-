<?php

class Cart {
    public int $id;
    public int $userId;
    public array $items = [];

    public function __construct(int $id, int $userId) {
        $this->id = $id;
        $this->userId = $userId;
    }

    public function addItem(CartItem $item): void {
        $this->items[] = $item;
    }

    public function getCartTotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getTotalPrice();
        }
        return $total;
    }
}