(function(){
  const fallbackUrl = '../pages/user_profile.html';
  const adminUrl = '../pages/profile.html';
  const signInUrl = '../pages/sign-in.html';

  function getCachedUrl(){
    const rol =
      sessionStorage.getItem('imssight_usuario_rol');

    if(rol === 'admin'){
      return adminUrl;
    }

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
        setProfileLinks(signInUrl);
        return signInUrl;
      }

      const rol =
        data.usuario?.rol || 'usuario';

      sessionStorage.setItem(
        'imssight_usuario_rol',
        rol
      );

      const url =
        rol === 'admin'
          ? adminUrl
          : fallbackUrl;

      setProfileLinks(url);
      return url;
    }
    catch(error){
      console.error(error);
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
