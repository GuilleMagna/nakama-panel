<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio para esta selección.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'url' => 'El campo :attribute debe ser una URL válida con el protocolo permitido.',
    'unique' => 'El valor de :attribute ya está registrado.',
    'exists' => 'El valor seleccionado para :attribute no existe.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'string' => 'El campo :attribute debe ser texto.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'date_format' => 'El campo :attribute debe tener formato :format.',
    'before_or_equal' => 'La fecha de :attribute no puede ser posterior a :date.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'min' => ['numeric' => 'El campo :attribute debe ser al menos :min.', 'string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'max' => ['numeric' => 'El campo :attribute no puede superar :max.', 'string' => 'El campo :attribute no puede superar :max caracteres.'],
    'attributes' => ['name' => 'nombre', 'client_id' => 'cliente', 'url' => 'URL del sitio', 'email' => 'correo', 'monitor_endpoint' => 'endpoint HTTPS', 'monitor_interval_hours' => 'intervalo de consulta', 'occurred_on' => 'fecha del hecho', 'title' => 'título', 'description' => 'descripción', 'password' => 'contraseña'],
];
