#!/usr/bin/env python3
"""
Query the FAISS index produced by `index_docs.py` or `index_user_docs.py`.

Usage: python scripts/query_docs.py "buscar texto" [k] [index_path] [meta_path]
If `index_path` is provided, `meta_path` defaults to same stem + '_metadata.json'.
Prints JSON: { results: [ {file, start, end, text, score}, ... ] }
"""
import json
import sys
import os
from pathlib import Path
import numpy as np
import faiss
from sentence_transformers import SentenceTransformer

try:
    import requests
except Exception:
    requests = None


OUT_DIR = Path(__file__).resolve().parent
DEFAULT_INDEX = OUT_DIR / 'index.faiss'


def resolve_paths(index_arg=None, meta_arg=None):
    if index_arg:
        idx = Path(index_arg)
    else:
        idx = DEFAULT_INDEX
    if meta_arg:
        meta = Path(meta_arg)
    else:
        meta = idx.with_name(idx.stem + '_metadata.json')
    return idx, meta


def load_index(path):
    p = Path(path)
    if not p.exists():
        raise FileNotFoundError('Index not found: ' + str(p))
    return faiss.read_index(str(p))


def load_meta(path):
    p = Path(path)
    if not p.exists():
        return []
    with open(p, 'r', encoding='utf-8') as fh:
        return json.load(fh)


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'query required'}))
        return
    query = sys.argv[1]
    k = int(sys.argv[2]) if len(sys.argv) > 2 else 5
    index_arg = sys.argv[3] if len(sys.argv) > 3 else None
    meta_arg = sys.argv[4] if len(sys.argv) > 4 else None

    model_name = 'all-MiniLM-L6-v2'
    model = SentenceTransformer(model_name)

    index_path, meta_path = resolve_paths(index_arg, meta_arg)
    index = load_index(index_path)
    meta = load_meta(meta_path)

    q_emb = model.encode([query], convert_to_numpy=True).astype('float32')
    faiss.normalize_L2(q_emb)

    D, I = index.search(q_emb, k)
    D = D[0].tolist()
    I = I[0].tolist()

    results = []
    for score, idx in zip(D, I):
        if idx < 0 or idx >= len(meta):
            continue
        item = meta[idx].copy()
        item['score'] = float(score)
        results.append(item)

    # Build a short aggregation of top snippets
    top_snips = [r.get('snippet', '') for r in results[:5]]
    answer = None

    openai_key = os.environ.get('OPENAI_API_KEY')
    openai_model = os.environ.get('OPENAI_MODEL', 'gpt-3.5-turbo')

    if openai_key and requests:
        # Call OpenAI Chat Completion for a concise, user-facing answer (Spanish)
        prompt = (
            f'Usa la siguiente información extraída de la documentación para responder de forma breve, natural y útil a la pregunta: "{query}". ' 
            'Sigue estas reglas: 1) Responde en español en un solo párrafo; 2) Sé claro y directo; 3) Si la información es insuficiente, indica qué pasos seguir o dónde buscar. ' 
            'Información (fragmentos):\n\n' + '\n\n'.join(top_snips)
        )
        try:
            url = 'https://api.openai.com/v1/chat/completions'
            headers = {'Authorization': f'Bearer {openai_key}', 'Content-Type': 'application/json'}
            data = {
                'model': openai_model,
                'messages': [
                    {'role': 'system', 'content': 'Eres un asistente que responde preguntas de usuario sobre un producto, de forma breve y útil.'},
                    {'role': 'user', 'content': prompt}
                ],
                'max_tokens': 256,
                'temperature': 0.2,
            }
            resp = requests.post(url, headers=headers, json=data, timeout=15)
            if resp.status_code == 200:
                j = resp.json()
                ans = j.get('choices', [])[0].get('message', {}).get('content', '').strip()
                if ans:
                    answer = ans
        except Exception:
            answer = None

    # Fallback: simple merged answer (cleaned) if no LLM available / failed
    if not answer:
        joined = '\n\n'.join([s.strip().replace('\n',' ') for s in top_snips if s.strip()])
        if joined:
            # build a short excerpt
            excerpt = joined
            if len(excerpt) > 800:
                excerpt = excerpt[:780] + '...'
            answer = excerpt

    out = {'results': results}
    if answer:
        out['answer'] = answer

    print(json.dumps(out, ensure_ascii=False))


if __name__ == '__main__':
    main()
