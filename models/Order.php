<?php

class Order {
    public int $id;
    public string $name;
    public string $status;
    public float $totalPrice;
    public int $userId;
    public Payment $payment;
    public array $items = [];

    public function __construct(int $id, string $name, string $status, float $totalPrice, int $userId, Payment $payment) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->totalPrice = $totalPrice;
        $this->userId = $userId;
        $this->payment = $payment;
    }

    public function addOrderItem(OrderItem $item): void {
        $this->items[] = $item;
    }
}