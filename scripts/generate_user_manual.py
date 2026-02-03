#!/usr/bin/env python3
"""
Generador básico de borrador de manual de usuario.

Analiza `routes/web.php` para extraer rutas principales por módulo
y concatena los archivos en `docs/` para producir `docs/manual_usuario_detailed.md`.

El resultado es un borrador que debe revisarse manualmente y enriquecerse
con pantallazos y pasos concretos. Está pensado como punto de partida para
mejorar las respuestas del chatbot.
"""
import re
import os
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
ROUTES = ROOT / 'routes' / 'web.php'
DOCS_DIR = ROOT / 'docs'
OUT = DOCS_DIR / 'manual_usuario_detailed.md'


def load_docs():
    parts = []
    if not DOCS_DIR.exists():
        return parts
    for p in sorted(DOCS_DIR.iterdir()):
        if p.is_file() and p.suffix.lower() in ('.md', '.txt'):
            try:
                text = p.read_text(encoding='utf-8')
            except Exception:
                text = ''
            parts.append((p.name, text[:2000]))
    return parts


def parse_routes():
    if not ROUTES.exists():
        return {}
    content = ROUTES.read_text(encoding='utf-8')
    # fallback: simple line-based parsing to extract method, path and optional name
    lines = content.splitlines()
    modules = {}
    for i, line in enumerate(lines):
        if 'Route::' not in line:
            continue
        m = re.search(r"Route::(get|post|put|patch|delete)\(", line, re.IGNORECASE)
        if not m:
            continue
        method = m.group(1).upper()
        path_m = re.search(r"\(\s*'([^']*)'", line)
        path = path_m.group(1) if path_m else ''
        name_m = re.search(r"->name\(\s*'([^']*)'\s*\)", line)
        # if name not on same line, check next two lines
        if not name_m:
            look = ' '.join(lines[i:i+3])
            name_m = re.search(r"->name\(\s*'([^']*)'\s*\)", look)
        name = name_m.group(1) if name_m else ''
        seg = path.strip('/').split('/')
        module = seg[0] if seg and seg[0] else 'root'
        modules.setdefault(module, []).append({'method': method, 'path': path, 'name': name})
    return modules


def human_module_title(key):
    mapping = {
        'leads': 'Leads',
        'customers': 'Clientes',
        'incidencias': 'Incidencias (Soporte / Backlog)',
        'calendar': 'Calendario',
        'postventa': 'Postventa',
        'users': 'Usuarios',
        'roles': 'Roles y permisos',
        'configuracion': 'Configuración',
        'desistidos': 'Desistidos',
        'espera': 'En espera',
        'certificados': 'Certificados',
        'contadores': 'Contadores',
    }
    return mapping.get(key, key.capitalize())


def make_manual(modules, docs):
    lines = []
    lines.append('# Manual de Usuario - Borrador')
    lines.append('Este documento es un borrador generado automáticamente. Revísalo y amplíalo con pasos, pantallazos y ejemplos específicos de tu organización.')
    lines.append('')
    lines.append('## Resumen de módulos')
    lines.append('')
    for k in sorted(modules.keys()):
        title = human_module_title(k)
        lines.append(f'### {title}')
        lines.append('')
        lines.append('Descripción:')
        lines.append(f'- Breve descripción de lo que hace el módulo **{title}**. (Editar)')
        lines.append('')
        lines.append('Acciones principales:')
        for r in modules[k]:
            name = f" `{r['name']}`" if r.get('name') else ''
            lines.append(f'- `{r["method"]}` `{r["path"]}`{name}')
        lines.append('')
        lines.append('Cómo usar (pasos):')
        lines.append('- Paso 1: ...')
        lines.append('- Paso 2: ...')
        lines.append('')
        lines.append('Permisos relacionados:')
        lines.append('- Indicar los permisos necesarios (ej: `leads.view`, `leads.create`)')
        lines.append('')
    lines.append('\n---\n')
    lines.append('## Documentos originales (extractos)')
    for name, excerpt in docs:
        lines.append(f'### {name}')
        lines.append('')
        lines.append('> ' + '\n> '.join(excerpt.strip().splitlines()))
        lines.append('')
    return '\n'.join(lines)


def main():
    docs = load_docs()
    modules = parse_routes()
    print(f'Found {len(modules)} modules, {len(docs)} docs files')
    out = make_manual(modules, docs)
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(out, encoding='utf-8')
    print('Wrote', OUT)


if __name__ == '__main__':
    main()
