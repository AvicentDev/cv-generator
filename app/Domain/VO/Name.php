<?php

class Name
{
  public string $name;
  public string $last_name;

  public function __construct(string $name, string $last_name)
  {
    $this->ensureNameIsValid($name);
    $this->ensureLastNameIsValid($last_name);

    $this->name = $name;
    $this->last_name = $last_name;
  }

  private function ensureNameIsValid(string $name): void
  {
    if (empty(trim($name))) {
      throw new InvalidArgumentException('El nombre no puede estar vacío.');
    }

    if (strlen($name) < 2) {
      throw new InvalidArgumentException('El nombre debe tener al menos 2 caracteres.');
    }

    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $name)) {
      throw new InvalidArgumentException('El nombre solo puede contener letras y espacios.');
    }
  }

  private function ensureLastNameIsValid(string $last_name): void
  {
    if (empty(trim($last_name))) {
      throw new InvalidArgumentException('El apellido no puede estar vacío.');
    }

    if (strlen($last_name) < 2) {
      throw new InvalidArgumentException('El apellido debe tener al menos 2 caracteres.');
    }

    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $last_name)) {
      throw new InvalidArgumentException('El apellido solo puede contener letras y espacios.');
    }
  }
}
