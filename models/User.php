<?php

class User {
    public int $id;
    public string $name;
    public string $email;
    private string $password;
    public string $role;
    public string $createdAt;
    public array $phones = [];
    public array $addresses = [];

    public function __construct(int $id, string $name, string $email, string $role, string $createdAt) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public function addPhone(string $phone): void {
        $this->phones[] = $phone;
    }

    public function addAddress(string $address): void {
        $this->addresses[] = $address;
    }
}