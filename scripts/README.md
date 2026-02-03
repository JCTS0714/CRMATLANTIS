# Embeddings + FAISS POC

Requirements (use a virtualenv):

```bash
python -m venv .venv
source .venv/bin/activate   # or .venv\Scripts\activate on Windows
pip install -r requirements.txt
```

Index docs:

```bash
python scripts/index_docs.py
```

Query the index:

```bash
python scripts/query_docs.py "¿Cómo crear un módulo?" 5
```

The PHP controller `app/Http/Controllers/ChatbotController.php` will call `scripts/query_docs.py` automatically when `scripts/index.faiss` exists.
