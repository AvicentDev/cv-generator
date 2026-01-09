<?php

class Name
{
  public string $name;


  public function __construct(string $name)
  {
    $this->ensureNameIsValid($name);

    $this->name = $name;
  }

  public function value()
  {
    return $this->name;
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
}
