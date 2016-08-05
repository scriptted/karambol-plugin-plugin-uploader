<?php

namespace PluginUploader\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use DashboardPlugin\Widget\Widget;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UploaderController extends Controller {

    public function mount(KarambolApp $app) {
      $app->get('/plugin-uploader', [$this, 'indexView'])->bind('admin_plugins_plugin_loader_index');
      ;

      $app->get('/plugin-uploader/require-ajax', [$this, 'requireAjax'])->bind('admin_plugins_plugin_loader_require_ajax');
      ;
    }

    public function indexView() {
      $twig = $this->get('twig');

      $client = new \Github\Client();
      $search = $client->api('search')->repositories('karambol-plugin', 'stars', 'desc');

      //dump($search);

      $repo = $client->api('repo')->releases()->all('scriptted', 'karambol-plugin-plugin-uploader');

      dump($repo);

      return $twig->render('plugins/plugin-uploader/index.html.twig', array('search' => $search));
    }

    public function requireAjax(){
        $request = $this->get('request');

        $package = $request->request->get('package');

        $appPath = $this->get('app_path');

        if($appPath->existsInApp('composer.phar')){
            //$process = new Process($appPath->getPath('composer.phar').' require '.$package);
            $process = new Process($appPath->getPath('composer.phar').' update');

            $process->start();
            foreach ($process as $type => $data) {
                echo $data.'<br>';
            }
        }
    }

}
