# Chatbot POC (búsqueda de documentación)

Este POC proporciona un endpoint simple que busca en la carpeta `docs/` los archivos que coinciden con la consulta del usuario y devuelve extractos relevantes. Objetivo: validar flujo RAG básico sin capacidades de LLM ni embeddings todavía.

Endpoint
- `POST /api/chatbot/query` (POC)
- Body JSON: `{ "q": "texto de la pregunta" }`

Qué devuelve
- JSON con `data` => array de up to 5 items: `{ file, score, snippet }`.

Limitaciones del POC
- Búsqueda textual simple (conteo de tokens), sin embeddings ni semántica.
- No autenticado — proteger en producción.
- Latencia proporcional al número de archivos en `docs/` (este POC lee archivos en cada request).

Pasos siguientes recomendados (mejoras)
1. Indexar e insertar en vector DB local (FAISS) y usar embeddings (`sentence-transformers`) para búsqueda semántica.
2. Añadir pipeline que precompute embeddings y actualice índice cuando se agreguen documentos.
3. Implementar RAG: recuperar top-k + opcional generación con LLM (o plantillas) para mejorar respuestas.
4. Proteger el endpoint con autenticación y cuota, y agregar caché para respuestas frecuentes.

Cómo probar localmente
1. Arranca servidor Laravel:
```bash
php artisan serve
```
2. Llamada ejemplo (curl):
```bash
curl -X POST http://127.0.0.1:8000/api/chatbot/query \
  -H "Content-Type: application/json" \
  -d '{"q": "¿cómo sincronizo permisos?"}'
```

Integración futura con interfaz
- Podemos exponer un modal/chat en el dashboard que consuma este endpoint y muestre las coincidencias y recomendaciones de acción.

Fin.
