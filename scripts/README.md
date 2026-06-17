# Scripts del proyecto

## Estructura

- `check_php_syntax.php` - chequeo corto usado por `composer syntax`
- `docs/` - utilidades de indexado/consulta documental
- `diagnostics/` - scripts de diagnóstico y reparación local
- `setup/` - scripts de preparación/configuración operativa
- `deploy/` - helpers puntuales de despliegue/manuales operativos

## Embeddings + FAISS para documentación

Requisitos (usar virtualenv):

```bash
python -m venv .venv
source .venv/bin/activate   # or .venv\Scripts\activate on Windows
pip install -r scripts/docs/requirements.txt
```

Indexar documentación general:

```bash
python scripts/docs/index_docs.py
```

Indexar solo manuales de usuario:

```bash
python scripts/docs/index_user_docs.py
```

Consultar el índice:

```bash
python scripts/docs/query_docs.py "¿Cómo crear un módulo?" 5
```

Los índices se escriben en `storage/app/ai/docs-search/`.

El controlador PHP `app/Http/Controllers/ChatbotController.php` ejecuta `scripts/docs/query_docs.py` automáticamente cuando existe `storage/app/ai/docs-search/index.faiss` o `storage/app/ai/docs-search/index_user.faiss`.
