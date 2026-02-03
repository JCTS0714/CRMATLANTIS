#!/usr/bin/env python3
"""
Index documents under `docs/` using SentenceTransformers + FAISS.

Produces:
- scripts/index.faiss          (FAISS index file)
- scripts/index_metadata.json  (JSON list of {id, file, start, end, text})

Usage: python scripts/index_docs.py
"""
import json
import os
from pathlib import Path
from sentence_transformers import SentenceTransformer
import numpy as np
import faiss
from tqdm import tqdm


DOCS_DIR = Path(__file__).resolve().parents[1] / 'docs'
OUT_DIR = Path(__file__).resolve().parent
INDEX_PATH = OUT_DIR / 'index.faiss'
META_PATH = OUT_DIR / 'index_metadata.json'


def load_files():
    exts = {'.md', '.txt', '.html'}
    files = []
    if DOCS_DIR.exists():
        for p in DOCS_DIR.rglob('*'):
            if p.is_file() and p.suffix.lower() in exts:
                files.append(p)
    return files


def chunk_text(text, chunk_size=500, overlap=100):
    start = 0
    L = len(text)
    while start < L:
        end = min(L, start + chunk_size)
        yield start, end, text[start:end].strip()
        if end == L:
            break
        start = max(0, end - overlap)


def main():
    model_name = 'all-MiniLM-L6-v2'
    print('Loading model', model_name)
    model = SentenceTransformer(model_name)

    files = load_files()
    print(f'Found {len(files)} document files under', DOCS_DIR)

    chunks = []
    for f in files:
        try:
            txt = f.read_text(encoding='utf-8')
        except Exception:
            try:
                txt = f.read_text(encoding='latin-1')
            except Exception:
                continue
        for start, end, snippet in chunk_text(txt, chunk_size=600, overlap=120):
            if len(snippet) < 20:
                continue
            chunks.append({'file': str(f.relative_to(Path.cwd())), 'start': start, 'end': end, 'text': snippet})

    print('Total chunks:', len(chunks))
    if len(chunks) == 0:
        print('No chunks to index; exiting.')
        return

    texts = [c['text'] for c in chunks]

    # compute embeddings in batches
    batch_size = 32
    embeddings = []
    for i in tqdm(range(0, len(texts), batch_size), desc='Embedding'):
        batch = texts[i:i+batch_size]
        emb = model.encode(batch, convert_to_numpy=True)
        embeddings.append(emb)
    embeddings = np.vstack(embeddings).astype('float32')

    # normalize for cosine-sim via inner product
    faiss.normalize_L2(embeddings)

    d = embeddings.shape[1]
    index = faiss.IndexFlatIP(d)
    index.add(embeddings)

    print('Writing index to', INDEX_PATH)
    faiss.write_index(index, str(INDEX_PATH))

    print('Writing metadata to', META_PATH)
    with open(META_PATH, 'w', encoding='utf-8') as fh:
        json.dump(chunks, fh, ensure_ascii=False)

    print('Done.')


if __name__ == '__main__':
    main()
