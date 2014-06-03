<?php
/**
 * Componente usado en los Controllers cuyo model principal tiene relación con Site 
 *
 * @package Website.Controller.Component 
 */
 
 App::uses('Component', 'Controller');

class SitesComponent extends Component 
{  

/**
 * Componentes a utilizar
 *
 * @var array
 */
  public $components = array( 'Session');
  
/**
 * Callback
 * Al inicializar el Component
 * 
 * Cuando se esté editando un sitio, se le setea al model principal las siguientes propiedades
 * Model::restrictSite
 * Model::sitePermalink
 * Estas propiedaes servirán a SitableBehavior para que al guardar un nuevo registro se coloque automáticamente site_id
 *
 * @param object $controller 
 */
  function initialize( Controller $controller)
  {    
    $this->Controller = $controller;
    $this->setAdminSite();
    $this->setFrontDomain( $this->Controller->request->host());
    
  }
  
  public function startup( Controller $controller)
  {

  }


/**
 * Setea en la configuración el site actual que se está editando
 *
 * @return void
 * @since Shokesu 0.1
 */
  function setAdminSite()
  {
    if( !isset( $this->Controller->request->params ['admin']))
    {
      return;
    }
    
    if( !empty( $this->Controller->request->params ['site']))
    {
      $this->Controller->loadModel( 'Site');

      $site = ClassRegistry::init( 'Site')->find( 'first', array(
          'conditions' => array(
              'Site.slug' => $this->Controller->request->params ['site']
          )       
      ));
      
      Configure::write( 'Website.current', $site);
    }
  }
  
/**
 * Se encarga de verificar y setear el dominio principal
 * Este dominio / subdominio servirá para todas las peticiones a los models que tengan como clave site_id
 *
 * @return void
 * @since Shokesu 0.1
 */
  function setFrontDomain( $domain)
  {
    // Si estamos en admin, retornamos
    if( isset( $this->Controller->request->params ['admin']))
    {
      return;
    }
    
    // Si el sitio ya está seteado, retornamos
    if( isset( $this->Controller->request->params ['site']))
    {
      return;
    }

    // Si dominio no existe se redirige al subdominio comercial
    if( $site = $this->getDomain( $domain))
    {      
      Configure::write( 'Website.current', $site);
      
      $this->Controller->request->addParams( array(
          'site' => $site ['Site']['slug'],
      ));
      
      if( $domain != $this->Controller->request->host())
      {
        $this->Controller->redirect( 'http://'. $domain);
      }
    }
    elseif( strpos( $domain, 'www.') !== false)
    {
      $domain = str_replace( 'www.', '', $domain);
      $this->setFrontDomain( $domain);
    }
    else
    {
      if( isset( $this->settings ['redirect']))
      {
        $this->Controller->redirect( $this->settings ['redirect']);
      }
    }
  }

/**
 * Devuelve el nombre del dominio dada la columna 'domain' del model Site
 *
 * @param string $domain la columna 'domain' del model Site
 * @return string El nombre del dominio
 * @since Shokesu 0.1
 */
  function hostName( $domain)
  {
    return Domain::hostName( $domain);
  }
  
/**
 * Verifica si un dominio existe en Site
 *
 * @param string $domain 
 * @return mixed El id del dominio si existe o false si no existe
 * @since Shokesu 0.1
 */
  public function getDomain( $domain)
  {
    $site = ClassRegistry::init( 'Site')->find( 'first', array(
        'conditions' => array(
            'Site.domain' => $domain
        ),
    ));

    return $site;
  }
  
/**
 * Redirige a la página principal del website, dado el id del website
 *
 * @param integer $site_id 
 * @return void
 * @since Shokesu 0.1
 */
  public function redirectToSite( $site_id)
  {
    $domain = $this->getDomainName( $site_id, true);
    $this->Controller->redirect( $domain);
  }

  public function getDomainName( $site_id, $http = false)
  {
    $this->Controller->loadModel( 'Site');
    
    $site = $this->Controller->Site->find( 'first', array(
        'conditions' => array(
            'Site.id' => $site_id
        ),
        'recursive' => -1,
        'fields' => array(
            'Site.domain'
        )
    ));
    
    $host = $this->hostName( $site ['Site']['domain']);
    
    if( $http)
    {
      $host = 'http://'. $host;
    }
    
    return $host;
  }
  
 
}