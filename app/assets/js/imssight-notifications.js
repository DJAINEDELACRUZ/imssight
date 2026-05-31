(function(){
  function escapar(valor){
    return String(valor ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function fechaNotificacion(fecha){
    if(!fecha){
      return '';
    }

    return new Date(fecha.replace(' ', 'T'))
      .toLocaleString('es-MX', {
        dateStyle:'medium',
        timeStyle:'short'
      });
  }

  function templateNotificacion(notificacion){
    const id =
      escapar(notificacion.id);

    const href =
      escapar(notificacion.url || '#');

    const leida =
      Number(notificacion.leida) === 1;

    const icono =
      escapar(notificacion.icono || 'notifications');

    const fecha =
      fechaNotificacion(notificacion.fecha);

    return `
      <li>
        <a
          class="dropdown-item border-radius-md ${leida ? '' : 'bg-gray-100'}"
          href="${href}"
          data-imssight-notification-id="${id}">
          <div class="d-flex py-1">
            <div class="my-auto">
              <i class="material-symbols-rounded ${leida ? 'text-secondary' : 'text-primary'}">
                ${icono}
              </i>
            </div>
            <div class="d-flex flex-column justify-content-center ms-3">
              <h6 class="text-sm font-weight-bold mb-1">
                ${escapar(notificacion.titulo)}
              </h6>
              <p class="text-xs text-secondary mb-0">
                ${escapar(notificacion.mensaje)}
              </p>
              ${
                fecha
                  ? `<small class="text-xs text-muted mt-1">${fecha}</small>`
                  : ''
              }
            </div>
          </div>
        </a>
      </li>
    `;
  }

  async function marcarComoLeida(id){
    if(!id || id === 'perfil'){
      return;
    }

    try{
      await fetch('../php/notificaciones.php', {
        method:'PATCH',
        cache:'no-store',
        headers:{
          'Content-Type':'application/json'
        },
        body:JSON.stringify({
          id:Number(id)
        })
      });
    }
    catch(error){
      console.error(error);
    }
  }

  async function cargarNotificaciones(){
    const dot =
      document.getElementById('notificationDot');

    const menu =
      document.getElementById('notificationMenu');

    if(!dot || !menu){
      return;
    }

    try{
      const response =
        await fetch('../php/notificaciones.php', {
          cache:'no-store'
        });

      const data =
        await response.json();

      if(!data.ok){
        throw new Error(data.mensaje || 'No se pudieron cargar las notificaciones.');
      }

      dot.style.display =
        data.no_leidas > 0
          ? 'inline-block'
          : 'none';

      if(!data.notificaciones || data.notificaciones.length === 0){
        menu.innerHTML = `
          <li>
            <div class="dropdown-item text-muted">
              Sin notificaciones
            </div>
          </li>
        `;
        return;
      }

      menu.innerHTML =
        data.notificaciones
          .map(templateNotificacion)
          .join('');
    }
    catch(error){
      console.error(error);
    }
  }

  window.cargarNotificacionesImssight =
    cargarNotificaciones;

  document.addEventListener('DOMContentLoaded', () => {
    cargarNotificaciones();

    setTimeout(cargarNotificaciones, 600);
    setTimeout(cargarNotificaciones, 1500);
    setInterval(cargarNotificaciones, 10000);

    document.addEventListener('click', event => {
      const link =
        event.target.closest('[data-imssight-notification-id]');

      if(!link){
        return;
      }

      marcarComoLeida(
        link.dataset.imssightNotificationId
      );
    });
  });
})();
