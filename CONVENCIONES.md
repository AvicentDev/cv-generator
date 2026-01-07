# Convenciones y Mejores Prácticas para Laravel

Este documento resume las convenciones y mejores prácticas para desarrollar en Laravel, basadas en principios como el **Single Responsibility Principle (SRP)**, DRY, y estándares de la comunidad. Aplica estos principios para mantener el código legible, mantenible y escalable en tu proyecto de generador de CV.

## 1. Single Responsibility Principle (SRP)
Una clase debe tener solo una responsabilidad.

- **Malo**: Un método que valida, registra logs y actualiza datos.
- **Bueno**: Separa responsabilidades en clases dedicadas (e.g., Request classes, Services).

## 2. Métodos deben hacer solo una cosa
Un método debe enfocarse en una sola tarea.

- **Malo**: Un accessor con lógica condicional compleja.
- **Bueno**: Divide en métodos pequeños y descriptivos.

## 3. Modelos gordos, controladores delgados
Coloca lógica relacionada con DB en modelos Eloquent.

- **Malo**: Consultas complejas en controladores.
- **Bueno**: Usa scopes y métodos en modelos.

## 4. Validación
Mueve validaciones de controladores a clases Request.

- **Malo**: Validación inline en controladores.
- **Bueno**: Usa `FormRequest` para reglas.

## 5. Lógica de negocio en clases Service
Controladores solo manejan HTTP; lógica de negocio en Services.

- **Malo**: Manejo de archivos en controladores.
- **Bueno**: Delega a `ArticleService`.

## 6. DRY (Don't Repeat Yourself)
Reutiliza código con scopes, traits, etc.

- **Malo**: Duplicación en queries.
- **Bueno**: Usa scopes Eloquent.

## 7. Prefiere Eloquent sobre Query Builder y SQL crudo
Usa Eloquent para legibilidad y herramientas built-in.

- **Malo**: Queries SQL manuales.
- **Bueno**: `Article::has('user.profile')->verified()->latest()->get()`.

## 8. Asignación masiva
Usa `$fillable` y métodos como `create()`.

- **Malo**: Asignación manual propiedad por propiedad.
- **Bueno**: `$category->article()->create($request->validated())`.

## 9. Evita N+1 y queries en Blade
Usa eager loading y no ejecutes queries en vistas.

- **Malo**: Loops sin `with()`.
- **Bueno**: `$users = User::with('profile')->get()`.

## 10. Chunk data para tareas pesadas
Procesa datos en chunks para evitar memoria alta.

- **Malo**: Procesar todo de una vez.
- **Bueno**: `$this->chunk(500, callback)`.

## 11. Nombres descriptivos sobre comentarios
Usa nombres claros en lugar de comentarios.

- **Malo**: Comentarios explicando lógica simple.
- **Bueno**: Método `hasJoins()`.

## 12. Separa JS/CSS de Blade y HTML de PHP
No mezcles capas.

- **Malo**: JS inline en Blade.
- **Bueno**: Usa `@json()` o archivos separados.

## 13. Usa config y archivos de idioma
Evita texto hardcodeado.

- **Malo**: Strings directos.
- **Bueno**: `__('app.article_added')`.

## 14. Herramientas estándar de Laravel
Prefiere built-ins sobre paquetes 3rd party.

- Ejemplos: Laravel Sail para dev, Eloquent para DB, Blade para templates.

## 15. Convenciones de nomenclatura
Sigue PSR y estándares Laravel:

- **Controladores**: Singular (e.g., `ArticleController`).
- **Rutas**: Plural (e.g., `articles/1`).
- **Modelos**: Singular (e.g., `User`).
- **Tablas**: Plural (e.g., `article_comments`).
- **Métodos**: camelCase (e.g., `getAll`).
- **Variables**: camelCase (e.g., `$articlesWithAuthor`).
- **Vistas**: kebab-case (e.g., `show-filtered.blade.php`).

## 16. Convention over Configuration
Sigue convenciones para evitar config extra.

- **Malo**: Overrides manuales.
- **Bueno**: Nombres estándar (e.g., tabla `customers`, PK `id`).

## 17. Sintaxis corta y legible
Usa helpers y sintaxis moderna.

- Ejemplos: `session('cart')` en lugar de `Session::get('cart')`.

## 18. Usa IoC/Service Container
Inyecta dependencias en lugar de `new Class`.

- **Malo**: `new User`.
- **Bueno**: Inyección en constructor.

## 19. No accedas .env directamente
Pasa datos a config files.

- **Malo**: `env('API_KEY')`.
- **Bueno**: `config('api.key')`.

## 20. Fechas en formato estándar
Usa Carbon y casts.

- **Malo**: Strings de fecha.
- **Bueno**: `$casts = ['ordered_at' => 'datetime']`.

## 21. Evita DocBlocks
Usa type hints y nombres descriptivos.

- **Malo**: DocBlocks largos.
- **Bueno**: `public function isValidAsciiString(string $string): bool`.

## Otras buenas prácticas
- Evita lógica en rutas.
- Minimiza PHP vanilla en Blade.
- Usa DB en memoria para tests.
- No overrides framework features.
- Usa PHP moderno, pero prioriza legibilidad.
- Evita View Composers a menos que sea necesario.

Aplica estas convenciones en tu proyecto para un código limpio y colaborativo. Si necesitas ejemplos específicos, consulta la documentación de Laravel.