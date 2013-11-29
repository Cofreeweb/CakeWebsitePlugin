<?php

/**
 * Sitable behavior class
 * 
 * Realiza labores en los registros de los models relacionados con el model Site.
 * 
 * @package Website.Model.Behavior
 */
class SitableBehavior extends ModelBehavior
{
  
  public function beforeSave( Model $Model)
  {
    if( empty( $this->id) 
        && Configure::read( 'Website.current.Site.id') 
        && $Model->hasField( 'site_id')
        && empty( $Model->data [$Model->alias]['site_id']))
    {
      $Model->data [$Model->alias]['site_id'] = Configure::read( 'Website.current.Site.id');
    }
    elseif( !empty( $Model->restrictSite))
    {
      $Model->data [$Model->alias]['site_id'] = $Model->restrictSite;
    }
    
    return true;
  }
  
/**
 * En `beforeFind()` se asegura de que todas las peticiones a los registros se hacen de forma restrictiva Sitio que se está editando
 * Se añadirá a `$query ['conditions']` las condiciones correctas para esa restricción
 *
 * @param object $Model 
 * @param array $query 
 * @return array El query para la petición de la búsqueda
 */
  function beforeFind( Model $Model, $query)
  {    
    $site_id = $this->getSiteId( $Model);
    
    if( empty( $Model->noSitable) && $site_id && $Model->hasField( 'site_id') && !isset( $query ['conditions'][$Model->alias .'.site_id'] ))
    {
      $query ['conditions'][$Model->alias .'.site_id'] = $site_id;
    }

    $Model->noSitable = false;
    return $query;  
  }
  

  
/**
 * Devuelve el id del sitio, tomando el parámetro en CakeRequest::params ['site']
 *
 * @param object $Model 
 * @param string $site Permalink del site
 * @return integer || false
 */
  public function getSiteId( Model $Model)
  {
    if( !empty( $Model->restrictSite))
    {
      return $Model->restrictSite;
    }
    
    return Configure::read( 'Website.current.Site.id');
  }
  
}

?>