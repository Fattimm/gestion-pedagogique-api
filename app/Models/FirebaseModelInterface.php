<?php

namespace App\Models;

interface FirebaseModelInterface
{
    public function syncToFirebase(string $operation = 'update');
    public function deleteFromFirebase();
    public function toArray(): array;
    public function setId($id): self;

    // Méthodes CRUD
    public function create(array $data): self;
    public function read(string $id): ?self;
    // public function update(array $data): self;
    public function update(array $attributes = [], array $options = []);
    public function delete(): void;
}
