(function(){
  let usuarios = [];
  let usuarioActual = null;
  let totalNoLeidosAnterior = null;
  let pollingActivo = false;

  function escapar(valor){
    return String(valor ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function fechaChat(fecha){
    if(!fecha){
      return '';
    }

    return new Date(fecha.replace(' ', 'T'))
      .toLocaleString('es-MX', {
        dateStyle:'short',
        timeStyle:'short'
      });
  }

  function normalizarUrlChat(valor){
    const texto =
      String(valor || '').trim();

    if(/^https?:\/\//i.test(texto)){
      return texto;
    }

    if(/^localhost(:\d+)?\//i.test(texto)){
      return `http://${texto}`;
    }

    if(/^\/pages\//i.test(texto)){
      return `..${texto}`;
    }

    if(/^pages\//i.test(texto)){
      return `../${texto}`;
    }

    if(/^[\w.-]+\.html(\?|#|$)/i.test(texto)){
      return `../pages/${texto}`;
    }

    return null;
  }

  function renderizarTextoChat(texto){
    const patron =
      /(https?:\/\/[^\s<>"']+|localhost(?::\d+)?\/[^\s<>"']+|\/pages\/[^\s<>"']+|pages\/[^\s<>"']+|[\w.-]+\.html(?:\?[^\s<>"']*)?)/gi;

    let ultimoIndice = 0;
    let resultado = '';
    let coincidencia;

    while((coincidencia = patron.exec(String(texto ?? ''))) !== null){
      const antes =
        String(texto ?? '').slice(ultimoIndice, coincidencia.index);

      const token =
        coincidencia[0].replace(/[).,;:!?]+$/, '');

      const sobrante =
        coincidencia[0].slice(token.length);

      const url =
        normalizarUrlChat(token);

      resultado += escapar(antes);

      if(url){
        resultado += `
          <a href="${escapar(url)}" class="quick-chat-link" target="_blank" rel="noopener noreferrer">
            ${escapar(token)}
          </a>
        `;
      }
      else{
        resultado += escapar(token);
      }

      resultado += escapar(sobrante);
      ultimoIndice = coincidencia.index + coincidencia[0].length;
    }

    resultado +=
      escapar(String(texto ?? '').slice(ultimoIndice));

    return resultado.replaceAll('\n', '<br>');
  }

  function asegurarPuntoChat(elemento){
    if(!elemento){
      return null;
    }

    const contenedor =
      elemento.closest('a') || elemento;

    contenedor.style.position =
      contenedor.style.position || 'relative';

    let punto =
      contenedor.querySelector('.imssight-chat-dot');

    if(!punto){
      punto =
        document.createElement('span');

      punto.className =
        'imssight-chat-dot';

      Object.assign(punto.style, {
        background:'#f44336',
        borderRadius:'50%',
        boxShadow:'0 0 10px rgba(244,67,54,.8)',
        display:'none',
        height:'10px',
        position:'absolute',
        right:'-1px',
        top:'1px',
        width:'11px',
        zIndex:'2'
      });

      contenedor.appendChild(punto);
    }

    return punto;
  }

  function actualizarPuntosChat(totalNoLeidos){
    const mostrar =
      Number(totalNoLeidos) > 0;

    document
      .querySelectorAll('.fixed-plugin-button, .fixed-plugin-button-nav')
      .forEach(elemento => {
        const punto =
          asegurarPuntoChat(elemento);

    if(punto){
      Object.assign(punto.style, {
        background:'#f44336',
        border:'0',
        borderRadius:'50%',
        boxShadow:'0 0 10px rgba(244,67,54,.8)',
        height:'10px',
        position:'absolute',
        right:'-1px',
        top:'1px',
        width:'10px',
        zIndex:'2'
      });

      punto.style.display =
        mostrar ? 'inline-block' : 'none';
    }
      });
  }

  async function obtenerConversacionesChat(){
    try{
      const response =
        await fetch('../php/chat.php?accion=conversaciones', {
          cache:'no-store'
        });

      const data =
        await response.json();

      if(!data.ok){
        return {
          totalNoLeidos:0,
          conversaciones:[]
        };
      }

      const conversaciones =
        data.usuarios || [];

      const totalNoLeidos =
        conversaciones.reduce((total, usuario) =>
          total + Number(usuario.sin_leer || 0),
          0
        );

      return {
        totalNoLeidos,
        conversaciones
      };
    }
    catch(error){
      console.error(error);
      return {
        totalNoLeidos:0,
        conversaciones:[]
      };
    }
  }

  async function actualizarPendientesChat(){
    const estado =
      await obtenerConversacionesChat();

    actualizarPuntosChat(estado.totalNoLeidos);

    if(totalNoLeidosAnterior === null){
      totalNoLeidosAnterior =
        estado.totalNoLeidos;
    }

    return estado;
  }

  async function refrescarChatEnVivo(){
    if(pollingActivo){
      return;
    }

    pollingActivo =
      true;

    try{
      const estado =
        await actualizarPendientesChat();

      const input =
        document.getElementById('quickChatBuscar');

      if(input && !input.value.trim()){
        usuarios =
          estado.conversaciones;

        const contenedor =
          document.getElementById('quickChatUsuarios');

        if(contenedor){
          await buscarUsuarios();
        }
      }

      if(usuarioActual && document.getElementById('quickChatFloating')){
        await refrescarSoloMensajes();
      }

      totalNoLeidosAnterior =
        estado.totalNoLeidos;
    }
    finally{
      pollingActivo =
        false;
    }
  }

  async function buscarUsuarios(){
    const input =
      document.getElementById('quickChatBuscar');

    const contenedor =
      document.getElementById('quickChatUsuarios');

    const titulo =
      document.getElementById('quickChatListaTitulo');

    if(!input || !contenedor){
      return;
    }

    const q =
      input.value.trim();

    const accion =
      q ? 'usuarios' : 'conversaciones';

    const response =
      await fetch(
        `../php/chat.php?accion=${accion}&q=${encodeURIComponent(q)}`,
        { cache:'no-store' }
      );

    const data =
      await response.json();

    if(!data.ok){
      return;
    }

    usuarios =
      data.usuarios || [];

    if(titulo){
      titulo.textContent =
        q ? 'Resultados de búsqueda' : 'Conversaciones recientes';
    }

    contenedor.innerHTML =
      usuarios.map(usuario => `
        <button
          class="quick-chat-user-btn ${usuarioActual && Number(usuarioActual.id) === Number(usuario.id) ? 'is-active' : ''}"
          type="button"
          onclick="seleccionarQuickChat(${Number(usuario.id)})">
          <span class="quick-chat-user-meta">
            <strong>${escapar(usuario.nombre)}</strong>
            ${Number(usuario.sin_leer || 0) > 0 ? `<span class="quick-chat-unread">${Number(usuario.sin_leer)}</span>` : ''}
          </span>
          <small>${escapar(usuario.rol)} · ${escapar(usuario.matricula)}</small>
          ${usuario.ultimo_mensaje ? `<span class="quick-chat-preview">${escapar(usuario.ultimo_mensaje)}</span>` : ''}
        </button>
      `).join('')
      || `<div class="text-muted text-sm">${q ? 'Sin usuarios encontrados.' : 'Sin conversaciones todavía. Busca una persona para comenzar.'}</div>`;
  }

  function cerrarPanelLateral(){
    const plugin =
      document.querySelector('.fixed-plugin');

    if(plugin){
      plugin.classList.remove('show');
    }
  }

  function crearVentanaFlotante(){
    let ventana =
      document.getElementById('quickChatFloating');

    if(ventana){
      ventana.classList.remove('is-minimized');
      return ventana;
    }

    ventana =
      document.createElement('section');

    ventana.id =
      'quickChatFloating';

    ventana.className =
      'quick-chat-floating';

    document.body.appendChild(ventana);

    return ventana;
  }

  function renderizarBurbujasChat(mensajes){
    return (
      mensajes.map(mensaje => {
        const mio =
          Number(mensaje.id_remitente) !== Number(usuarioActual.id);

        return `
          <div class="quick-chat-bubble ${mio ? 'is-mine' : 'is-theirs'}">
            <div>${renderizarTextoChat(mensaje.contenido)}</div>
            <small>${fechaChat(mensaje.fecha)}</small>
          </div>
        `;
      }).join('')
      || '<div class="text-muted text-sm text-center mt-5">Sin mensajes.</div>'
    );
  }

  async function obtenerMensajesActuales(){
    if(!usuarioActual){
      return [];
    }

    const response =
      await fetch(
        `../php/chat.php?usuario_id=${Number(usuarioActual.id)}`,
        { cache:'no-store' }
      );

    const data =
      await response.json();

    if(!data.ok){
      return [];
    }

    return data.mensajes || [];
  }

  async function refrescarSoloMensajes(){
    const lista =
      document.getElementById('quickChatMensajes');

    if(!usuarioActual || !lista){
      return;
    }

    const estabaAlFinal =
      lista.scrollHeight - lista.scrollTop - lista.clientHeight < 90;

    const mensajes =
      await obtenerMensajesActuales();

    lista.innerHTML =
      renderizarBurbujasChat(mensajes);

    if(estabaAlFinal){
      lista.scrollTop =
        lista.scrollHeight;
    }
  }

  async function cargarMensajes(enfocar = true){
    if(!usuarioActual){
      return;
    }

    const mensajes =
      await obtenerMensajesActuales();

    const ventana =
      crearVentanaFlotante();

    const borrador =
      document.getElementById('quickChatContenido')?.value || '';

    ventana.innerHTML = `
      <div class="quick-chat-floating-header">
        <div class="quick-chat-floating-title">
          <strong>
            <a href="../pages/user_profile.html?id=${Number(usuarioActual.id)}">
              ${escapar(usuarioActual.nombre)}
            </a>
          </strong>
          <small>${escapar(usuarioActual.rol)}</small>
        </div>
        <div class="quick-chat-floating-actions">
          <button
            class="quick-chat-icon-btn"
            type="button"
            title="Minimizar"
            aria-label="Minimizar chat"
            onclick="minimizarQuickChat()">
            <i class="material-symbols-rounded">remove</i>
          </button>
          <button
            class="quick-chat-icon-btn"
            type="button"
            title="Cerrar"
            aria-label="Cerrar chat"
            onclick="cerrarQuickChat()">
            <i class="material-symbols-rounded">close</i>
          </button>
        </div>
      </div>
      <div class="quick-chat-floating-body" id="quickChatMensajes">
        ${renderizarBurbujasChat(mensajes)}
      </div>
      <div class="quick-chat-floating-form">
        <input
          id="quickChatContenido"
          autocomplete="off"
          autocapitalize="sentences"
          autocorrect="off"
          maxlength="2000"
          placeholder="Escribe un mensaje..."
          spellcheck="false">
        <button
          class="quick-chat-send"
          type="button"
          aria-label="Enviar mensaje"
          onclick="enviarQuickChat()">
          <i class="material-symbols-rounded">send</i>
        </button>
      </div>
      <div id="quickChatAviso" class="px-3 pb-2"></div>
    `;

    const lista =
      document.getElementById('quickChatMensajes');

    if(lista){
      lista.scrollTop = lista.scrollHeight;
    }

    const input =
      document.getElementById('quickChatContenido');

    if(input){
      if(!enfocar && borrador){
        input.value =
          borrador;
      }

      if(enfocar){
        input.focus();
      }

      input.addEventListener('keydown', event => {
        if(event.key === 'Enter'){
          event.preventDefault();
          window.enviarQuickChat();
        }
      });
    }

    await actualizarPendientesChat();
  }

  window.abrirQuickChatUsuario = async function(usuario){
    usuarioActual = usuario;

    cerrarPanelLateral();
    await cargarMensajes();
    await buscarUsuarios();
  };

  window.seleccionarQuickChat = async function(id){
    const usuario =
      usuarios.find(usuario => Number(usuario.id) === Number(id));

    if(!usuario){
      return;
    }

    await window.abrirQuickChatUsuario(usuario);
  };

  window.cerrarQuickChat = function(){
    const ventana =
      document.getElementById('quickChatFloating');

    if(ventana){
      ventana.remove();
    }
  };

  window.minimizarQuickChat = function(){
    const ventana =
      document.getElementById('quickChatFloating');

    if(ventana){
      ventana.classList.toggle('is-minimized');
    }
  };

  window.enviarQuickChat = async function(){
    const input =
      document.getElementById('quickChatContenido');

    if(!usuarioActual || !input || !input.value.trim()){
      return;
    }

    const response =
      await fetch('../php/chat.php', {
        method:'POST',
        headers:{
          'Content-Type':'application/json'
        },
        body:JSON.stringify({
          id_destinatario:Number(usuarioActual.id),
          contenido:input.value.trim()
        })
      });

    const data =
      await response.json();

    if(!data.ok){
      const aviso =
        document.getElementById('quickChatAviso');

      if(aviso){
        aviso.innerHTML = `<div class="alert alert-danger py-2 mb-0">${escapar(data.mensaje || 'No se pudo enviar.')}</div>`;
      }
      return;
    }

    input.value = '';
    await refrescarSoloMensajes();
    await buscarUsuarios();
    await actualizarPendientesChat();
  };

  async function montarComponente(){
    const card =
      document.querySelector('.fixed-plugin .card');

    if(!card){
      return;
    }

    const response =
      await fetch('../pages/componentes/chat_rapido.html', {
        cache:'no-store'
      });

    card.innerHTML =
      await response.text();

    const close =
      card.querySelector('.fixed-plugin-close-button');

    if(close){
      close.addEventListener('click', () => {
        const plugin =
          document.querySelector('.fixed-plugin');

        if(plugin){
          plugin.classList.remove('show');
        }
      });
    }

    const input =
      document.getElementById('quickChatBuscar');

    if(input){
      input.addEventListener('input', buscarUsuarios);
    }

    await buscarUsuarios();
    await actualizarPendientesChat();
  }

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(montarComponente, 250);
    setTimeout(actualizarPendientesChat, 650);
    setTimeout(actualizarPendientesChat, 1600);
    setInterval(refrescarChatEnVivo, 4000);
  });
})();
