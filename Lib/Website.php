<?php 

/**
* 
*/
class Website
{
  
  public function get( $path)
  {
    return Configure::read( "Website.current.Site.$path");
  }
}