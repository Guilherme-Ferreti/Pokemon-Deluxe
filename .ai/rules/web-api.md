---
paths:
  - 'src/App/**/Http/{Web,Api}/routes.php'
---

# Web Api

## Register routes with controller class references, never closures
Route files only reference controllers ([Controller::class, 'method'] or invokable Controller::class; Route::inertia() for static pages); never inline closures. Route names come from the {domain}.{web|api}. prefixes applied in bootstrap/app.php; the route file adds only the final segment, name, and middleware.
