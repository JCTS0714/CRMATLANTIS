{{-- Embeddable chat fragment (no head/body) --}}
<div class="chat-embed" style="width:360px;">
  <div class="header"><h3 style="margin:0;font-size:15px">Chat de ayuda</h3><div class="small" style="font-size:12px;color:#6b7280">Info para usuarios</div></div>
  <div class="messages" id="embed-messages" style="height:320px;overflow:auto;padding:12px;background:#fff"></div>
  <div class="composer" style="display:flex;gap:8px;padding:8px;border-top:1px solid #eef2f7">
    <input id="embed-q" placeholder="Escribe tu pregunta..." style="flex:1;padding:8px;border:1px solid #e6eef8;border-radius:6px" />
    <button id="embed-send" style="padding:8px 10px;border-radius:6px;background:#1366d6;color:#fff;border:0">Enviar</button>
  </div>
</div>

<script>
  (function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    async function postQuery(q){
      const res = await fetch('/api/chatbot/query', {
        method:'POST', credentials:'same-origin', headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN': token,'Accept':'application/json'}, body: new URLSearchParams({q})
      });
      return res.json();
    }
    const messages = document.getElementById('embed-messages');
    function append(role, text){ const d=document.createElement('div'); d.style.margin='8px 0'; d.textContent = text; messages.appendChild(d); messages.scrollTop = messages.scrollHeight; }
    async function send(){
      const q = document.getElementById('embed-q').value.trim();
      if(!q) return;
      document.getElementById('embed-q').value='';
      append('user', q);
      append('bot','Buscando...');
      try{
        const r = await postQuery(q);
        messages.lastChild.remove();
        // Prefer a synthesized single answer when available
        if(r && r.answer){
          append('bot', r.answer);
        } else if(r && r.data && r.data.length){
          // backward compatible: show concatenated snippets
          let text = r.data.slice(0,3).map(it => (it.snippet || '') + (it.file? ' — ' + it.file.split('\\').pop():''));
          append('bot', text.join('\n\n'));
        } else {
          append('bot','No encontré información en la documentación de usuario.');
        }
      }catch(e){ messages.lastChild.remove(); append('bot','Error consultando servicio'); }
    }
    document.getElementById('embed-send').addEventListener('click', send);
    document.getElementById('embed-q').addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); send(); } });
  })();
</script>
