<?php

namespace GaShowStats
{
  class GaShowStats extends \WpMvc\BaseController
  {
    public function index()
    {
      global $site;
      $this->render( $this, "index" );
    }
  }
}
