<?php

namespace PluginUploader\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use DashboardPlugin\Widget\Widget;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Response;

class UploaderController extends Controller {

    public function mount(KarambolApp $app) {
      $app->get('/plugin-uploader', [$this, 'indexView'])->bind('admin_plugins_plugin_loader_index');

      $app->post('/plugin-uploader/require-ajax', [$this, 'requireAjax'])->bind('admin_plugins_plugin_loader_require_ajax');
      $app->post('/plugin-uploader/remove-ajax', [$this, 'removeAjax'])->bind('admin_plugins_plugin_loader_remove_ajax');

      $app->get('/plugin-uploader/progress-ajax', [$this, 'progressAjax'])->bind('admin_plugins_plugin_loader_progress_ajax');
    }

    public function indexView() {
      $twig = $this->get('twig');

      $client = new \Github\Client();

      $search = $client->api('search')->repositories('karambol-plugin', 'stars', 'desc');

      //dump($search);

      //TODO find better way to get latest release for each repo
      //$repo = $client->api('repo')->releases()->all('scriptted', 'karambol-plugin-plugin-uploader');

      //dump($repo);

      return $twig->render('plugins/plugin-uploader/index.html.twig', array('search' => $search));
    }

    public function requireAjax(){
        $request = $this->get('request');
        $session = $this->get('session');

        $temp = tempnam(sys_get_temp_dir(), 'progress');
        $session->set('composerProgress', $temp);
        session_write_close();

        $package = $request->request->get('package');

        $appPath = $this->get('app_path');

        putenv("COMPOSER_HOME=".$appPath->getPath('.composer'));

        $process = new Process('composer require '.$package.':dev-develop');
        $process->setWorkingDirectory($appPath->getPath(''));
        //$process = new Process($appPath->getPath('composer.phar').' update');

        $process->start();
        $composerProgress = '';
        foreach ($process as $type => $data) {
            $composerProgress .= $data.'<br>';
            $handle = fopen($temp, 'wa+');
            fwrite($handle, $composerProgress);
            fclose($handle);
        }

        $AJAXResponse['success'] = 'true';

		$response = new Response(json_encode($AJAXResponse));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
    }

    public function removeAjax(){
        $request = $this->get('request');
        $session = $this->get('session');

        $temp = tempnam(sys_get_temp_dir(), 'progress');
        $session->set('composerProgress', $temp);
        session_write_close();

        $package = $request->request->get('package');

        $appPath = $this->get('app_path');

        putenv("COMPOSER_HOME=".$appPath->getPath('.composer'));

        $process = new Process('composer remove '.$package);
        $process->setWorkingDirectory($appPath->getPath(''));

        $process->start();
        $composerProgress = '';
        foreach ($process as $type => $data) {
            $composerProgress .= $data.'<br>';
            $handle = fopen($temp, 'wa+');
            fwrite($handle, $composerProgress);
            fclose($handle);
        }

        $AJAXResponse['success'] = 'true';

		$response = new Response(json_encode($AJAXResponse));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
    }

    public function progressAjax(){
        $session = $this->get('session');

        $composerProgress = $session->get('composerProgress');

        $handle = fopen($composerProgress, 'r');
        $progress = fread($handle, filesize($composerProgress));
        fclose($handle);

        $AJAXResponse['composerProgress'] = $progress;
        $AJAXResponse['success'] = 'true';

		$response = new Response(json_encode($AJAXResponse));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
    }

}
