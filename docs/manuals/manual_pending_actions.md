# Acciones pendientes para mejorar el chatbot y el manual

Este documento lista acciones concretas para completar la mejora del manual de usuario y del sistema de respuestas del chatbot. Lo podemos usar como check-list y plan para la siguiente sesión.

1) Revisar y enriquecer `docs/manual_usuario_detailed.md`
- Asignar responsables por módulo (ej. Juan: Leads y Clientes; María: Incidencias y Postventa).
- Añadir pasos detallados, pantallazos y ejemplos reales por cada flujo.
- Prioridad: `Leads`, `Clientes`, `Incidencias`, `Calendario`.

2) Reindexar documentación de usuario
- Ejecutar `scripts/index_user_docs.py` después de actualizar el manual.
- Verificar que `scripts/index_user.faiss` y `scripts/index_user_metadata.json` se regeneren correctamente.

3) Elegir y configurar modelo para síntesis de respuestas
- Opciones:
  - `gpt-4o` / `gpt-4` (si disponible): mejor calidad y síntesis, mayor coste.
  - `gpt-3.5-turbo` / `gpt-3.5-turbo-16k`: equilibrio coste/beneficio.
  - Modelos locales (llama.cpp, Mistral local): sin coste por token pero mayor complejidad operativa.
- Recomendación inicial: `gpt-3.5-turbo` (buen trade-off). Cambiar a `gpt-4` si se necesita mayor calidad y presupuesto lo permite.
- Acciones:
  - Añadir `OPENAI_API_KEY` y `OPENAI_MODEL` a las variables de entorno en el servidor.
  - Probar con `scripts/query_docs.py` y revisar prompts y temperatura (`temperature=0.2` recomendado para respuestas determinísticas).

4) Integración y fallback
- Verificar que `app/Http/Controllers/ChatbotController.php` prefiera la `answer` y, si no, presente `data` limpia.
- Implementar límites de tamaño y manejo de errores (timeouts, rate limits).

5) Tests y CI
- Añadir pruebas automáticas que:
  - Verifiquen que `scripts/index_user.faiss` existe tras indexado.
  - Chequeen que `POST /api/chatbot/query` responde con `answer` o `data`.
- Agregar job en CI para validar indexado en PRs (opcional con cache).

6) Interfaz y UX
- Revisar mensajes del widget para asegurar que las respuestas se muestren en un solo párrafo y que haya opción "Más detalles" que despliegue los snippets.
- Añadir link en la respuesta a la sección del manual correspondiente (si `meta` contiene archivo/line).

7) Observabilidad
- Loggear consultas y respuestas en `storage/logs/chatbot.log` (anonimizar datos sensibles).
- Crear métricas básicas: tasa de acierto (usuario marca "útil"), latencia, fallos.

8) Operaciones
- Planificar reindexado periódico (cron) cuando el directorio `docs/` cambie.
- Incluir script para regenerar índice en desplegables/CI.

---

Si quieres, mañana puedo:
- (A) Empezar a añadir pantallazos y pasos concretos para `Leads` y `Clientes` en el manual.
- (B) Probar la integración con `gpt-3.5-turbo` usando tu `OPENAI_API_KEY` (si la proporcionas) y ajustar prompts.
- (C) Añadir tests y job de CI básicos.

Indica qué quieres priorizar mañana y lo avanzo.