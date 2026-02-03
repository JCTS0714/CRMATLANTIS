<!doctype html>
<html lang="es">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  <title>Chat de ayuda</title>
  <style>
    body { font-family: system-ui, Arial, sans-serif; margin: 0; padding: 0; }
    .chat { max-width: 720px; margin: 24px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
    .messages { height: 400px; overflow: auto; padding: 16px; background: #fafafa; }
    .msg { margin-bottom: 12px; }
    .msg.user { text-align: right; }
    .msg .bubble { display: inline-block; padding: 8px 12px; border-radius: 12px; }
    .msg.user .bubble { background: #1f6feb; color: white; }
    .msg.bot .bubble { background: #eee; color: #111; }
    .composer { display:flex; border-top:1px solid #eee; }
    .composer input { flex:1; padding:12px; border:0; }
    .composer button { padding:12px 16px; border:0; background:#1f6feb; color:#fff; cursor:pointer; }
  </style>
</head>
<body>
  <div class="chat" id="chat">
    <div class="messages" id="messages"></div>
    <div class="composer">
      <input id="q" placeholder="Escribe tu pregunta para el equipo de ayuda..." />
      <button id="send">Enviar</button>
    </div>
  </div>

  <script>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
      <title>Chat de ayuda</title>
      <style>
        :root{--bg:#f7fafc;--muted:#6b7280;--primary:#1f6feb;--bot:#f3f4f6}
        body { font-family: Inter, system-ui, Arial, sans-serif; margin: 0; padding: 24px; background: var(--bg); }
        .chat { max-width: 820px; margin: 8px auto; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(16,24,40,0.06); background: white; display:flex; flex-direction:column; }
        .header { padding:12px 16px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; gap:12px }
        .header h2{margin:0;font-size:16px}
        .messages { height:520px; overflow:auto; padding:18px; background: linear-gradient(180deg,#fff 0%, #fbfdff 100%); }
        .msg { margin-bottom:14px; display:flex; }
        .msg.user { justify-content:flex-end }
        .bubble { max-width:78%; padding:12px 14px; border-radius:14px; line-height:1.45; box-shadow:0 1px 0 rgba(0,0,0,0.03); }
        .msg.user .bubble { background:var(--primary); color:#fff; border-bottom-right-radius:4px; }
        .msg.bot { justify-content:flex-start }
        .msg.bot .bubble { background:var(--bot); color:#111; border-bottom-left-radius:4px; }
        .meta { margin-top:8px; font-size:12px; color:var(--muted) }
        .composer { display:flex; gap:0; align-items:center; border-top:1px solid #eef2f7; padding:8px; }
        .composer input { flex:1; padding:12px 14px; border-radius:8px; border:1px solid #e6eef8; outline:none; }
        .composer button { margin-left:8px; padding:10px 16px; border-radius:8px; border:0; background:var(--primary); color:#fff; cursor:pointer }
        .small { font-size:13px; color:var(--muted) }
        .spinner { display:inline-block; width:18px; height:18px; border-radius:50%; border:3px solid #e2e8f0; border-top-color:var(--primary); animation:spin 1s linear infinite; vertical-align:middle }
        @keyframes spin{to{transform:rotate(1turn)}}
        .readmore{ display:block; margin-top:8px; color:var(--primary); font-weight:600; cursor:pointer; font-size:13px }
      </style>
    </head>
    <body>
      <div class="chat" id="chat">
        <div class="header"><h2>Chat de ayuda</h2><div class="small">Información para usuarios</div></div>
        <div class="messages" id="messages" aria-live="polite"></div>
        <div class="composer">
          <input id="q" placeholder="Escribe tu pregunta para el equipo de ayuda..." autocomplete="off" />
          <button id="send">Enviar</button>
        </div>
      </div>
  
      <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        async function postQuery(q){
          const res = await fetch('/api/chatbot/query', {
            method:'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type':'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: new URLSearchParams({q})
          });
          return res.json();
        }
    
        const messages = document.getElementById('messages');
        function scrollToBottom(){ messages.scrollTop = messages.scrollHeight; }
    
        function createBubble(role, contentHTML){
          const d = document.createElement('div'); d.className = 'msg '+role;
          const b = document.createElement('div'); b.className = 'bubble'; b.innerHTML = contentHTML;
          d.appendChild(b); messages.appendChild(d); scrollToBottom(); return b;
        }
    
        function appendUser(text){ createBubble('user', escapeHtml(text)); }
    
        function appendBotSnippet(snippet, file){
          const max = 260;
          const full = snippet || '';
          const fileName = file ? file.split('\\').pop().split('/').pop() : '';
          const short = full.length > max ? full.slice(0,max).trim() + '…' : full;
          const id = 'm' + Math.random().toString(36).slice(2,9);
          const html = '<div class="text">' + escapeHtml(short) + '</div>' +
                       (full.length > max ? '<a class="readmore" data-id="'+id+'">Leer más</a>' : '') +
                       '<div class="meta small">' + (fileName ? 'Fuente: ' + escapeHtml(fileName) : '') + '</div>' +
                       '<div style="display:none" id="'+id+'">' + escapeHtml(full) + '</div>';
          const bubble = createBubble('bot', html);
          const rm = bubble.querySelector('.readmore');
          if(rm){ rm.addEventListener('click', ()=>{
            const hidden = document.getElementById(rm.getAttribute('data-id'));
            if(hidden){
              bubble.querySelector('.text').textContent = hidden.textContent;
              rm.style.display = 'none';
            }
          }); }
        }
    
        function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    
        async function send(){
          const qInput = document.getElementById('q');
          const q = qInput.value.trim(); if(!q) return; qInput.value = '';
          appendUser(q);
          const loading = createBubble('bot','<span class="spinner"></span> <span class="small">Buscando respuestas...</span>');
          try{
            const data = await postQuery(q);
            // remove loading bubble
            loading.parentElement.remove();
            if(data && data.data && data.data.length){
              data.data.forEach(r => appendBotSnippet(r.snippet, r.file));
            } else {
              createBubble('bot','No encontré información en la documentación de usuario.');
            }
          }catch(e){
            loading.parentElement.remove();
            createBubble('bot','Error consultando el servicio. Intenta de nuevo.');
          }
        }
    
        document.getElementById('send').addEventListener('click', send);
        document.getElementById('q').addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); send(); } });
      </script>
    </body>
    </html>
