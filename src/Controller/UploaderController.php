<?php

namespace PluginUploader\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use DashboardPlugin\Widget\Widget;

class UploaderController extends Controller {

    public function mount(KarambolApp $app) {
      $app->get('/plugin-uploader', [$this, 'indexView'])->bind('admin_plugins_plugin_loader_index');
      ;
    }

    public function indexView() {
      $twig = $this->get('twig');

      $client = new \Github\Client();
      $search = $client->api('search')->repositories('karambol-plugin', 'stars', 'desc');

      dump($search);

      return $twig->render('plugins/plugin-uploader/index.html.twig', array('search' => $search));
    }

}
