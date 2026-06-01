(function(){
  const fallbackUrl = '../pages/user_profile.html';
  const adminUrl = '../pages/profile.html';
  const signInUrl = '../pages/sign-in.html';

  function getCachedUrl(){
    const rol =
      sessionStorage.getItem('imssight_usuario_rol');

    if(rol){
      return fallbackUrl;
    }

    return null;
  }

  function setProfileLinks(url, resolved = true){
    document
      .querySelectorAll('[data-imssight-profile-link]')
      .forEach(link => {
        link.href = url;
        link.dataset.profileResolved =
          resolved ? 'true' : 'false';
      });
  }

  function syncAdminLinks(rol){
    document
      .querySelectorAll('[data-imssight-profile-link]')
      .forEach(link => {
        const menu =
          link.closest('.dropdown-menu');

        if(!menu){
          return;
        }

        const existing =
          menu.querySelector('[data-imssight-admin-link]');

        if(rol !== 'admin'){
          existing?.remove();
          return;
        }

        if(existing){
          return;
        }

        const item =
          document.createElement('li');

        item.setAttribute(
          'data-imssight-admin-link',
          'true'
        );

        item.innerHTML = `
          <a
            class="dropdown-item border-radius-md"
            href="${adminUrl}">
            <i class="fas fa-user-shield me-2"></i>
            Administrar
          </a>
        `;

        const profileItem =
          link.closest('li');

        if(profileItem?.nextSibling){
          menu.insertBefore(
            item,
            profileItem.nextSibling
          );
        }
        else{
          menu.appendChild(item);
        }
      });
  }

  async function resolveProfileUrl(force = false){
    const cachedUrl = getCachedUrl();

    if(cachedUrl && !force){
      setProfileLinks(cachedUrl);
      return cachedUrl;
    }

    try{
      const response =
        await fetch('../php/auth.php');

      const data =
        await response.json();

      if(!data.auth){
        syncAdminLinks(null);
        setProfileLinks(signInUrl);
        return signInUrl;
      }

      const rol =
        data.usuario?.rol || 'usuario';

      sessionStorage.setItem(
        'imssight_usuario_rol',
        rol
      );

      syncAdminLinks(rol);
      setProfileLinks(fallbackUrl);
      return fallbackUrl;
    }
    catch(error){
      console.error(error);
      syncAdminLinks(null);
      setProfileLinks(fallbackUrl);
      return fallbackUrl;
    }
  }

  window.resolveImssightProfileUrl =
    resolveProfileUrl;

  document.addEventListener('DOMContentLoaded', () => {
    setProfileLinks(
      getCachedUrl() || fallbackUrl,
      false
    );

    syncAdminLinks(
      sessionStorage.getItem('imssight_usuario_rol')
    );

    resolveProfileUrl(true);

    document.addEventListener('click', async event => {
      const logoutLink =
        event.target.closest(
          'a[href*="../php/logout.php"]'
        );

      if(logoutLink){
        sessionStorage.removeItem(
          'imssight_usuario_rol'
        );

        syncAdminLinks(null);

        return;
      }

      const link =
        event.target.closest(
          '[data-imssight-profile-link]'
        );

      if(!link){
        return;
      }

      event.preventDefault();

      const url =
        link.dataset.profileResolved === 'true'
          ? link.href
          : await resolveProfileUrl(true);

      window.location.href = url;
    });
  });
})();
