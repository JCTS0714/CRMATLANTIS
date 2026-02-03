@auth
<div id="chat-widget-root">
  <style>
    #chat-fab{position:fixed;right:18px;bottom:18px;z-index:1200}
    #chat-fab button{width:56px;height:56px;border-radius:50%;border:0;background:#1366d6;color:#fff;box-shadow:0 6px 18px rgba(16,24,40,0.15);cursor:pointer}
    #chat-modal{position:fixed;right:18px;bottom:88px;z-index:1200;display:none}
    #chat-modal .card{width:380px;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(2,6,23,0.2);overflow:hidden}
    #chat-modal .header{padding:10px 12px;border-bottom:1px solid #eef2f7}
  </style>

  <div id="chat-fab"><button id="chat-open" title="Abrir ayuda">💬</button></div>

  <div id="chat-modal" role="dialog" aria-hidden="true">
    <div class="card">
      <div class="header"><strong>Ayuda</strong><button id="chat-close" style="float:right;background:transparent;border:0;font-size:14px;cursor:pointer">✕</button></div>
      <div id="chat-embed-container" style="padding:10px;background:#f8fafc"></div>
    </div>
  </div>

  <script>
    (function(){
      const open = document.getElementById('chat-open');
      const modal = document.getElementById('chat-modal');
      const close = document.getElementById('chat-close');
      const container = document.getElementById('chat-embed-container');
      open.addEventListener('click', async ()=>{
        if(container.innerHTML.trim()===''){
          // fetch embed fragment and inject
          try{
            const res = await fetch('/chat/widget', {credentials:'same-origin'});
            if(res.ok){
              const html = await res.text();
              container.innerHTML = html;
              // Execute any inline scripts included in the fetched fragment
              const scripts = Array.from(container.querySelectorAll('script'));
              scripts.forEach(s => {
                const ns = document.createElement('script');
                if (s.src) {
                  ns.src = s.src;
                } else {
                  ns.textContent = s.textContent;
                }
                document.body.appendChild(ns);
                s.parentNode && s.parentNode.removeChild(s);
              });
            } else { container.innerHTML = '<div style="padding:12px">No se pudo cargar el chat.</div>'; }
          }catch(e){ container.innerHTML = '<div style="padding:12px">Error cargando chat.</div>'; }
        }
        modal.style.display='block'; modal.setAttribute('aria-hidden','false');
      });
      close.addEventListener('click', ()=>{ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); });
    })();
  </script>
</div>
@endauth
