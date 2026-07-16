<?php

class Payment {
    public int $id;
    public string $method;
    public string $status;
    public ?string $paidAt;

    public function __construct(int $id, string $method, string $status, ?string $paidAt = null) {
        $this->id = $id;
        $this->method = $method;
        $this->status = $status;
        $this->paidAt = $paidAt;
    }
}