<?php

namespace PluginUploader;

use Karambol\KarambolApp;
use Karambol\Plugin\PluginInterface;
use Karambol\Controller\Controller;
use Karambol\Menu\Menus;

class PluginUploader implements PluginInterface {

  public function boot(KarambolApp $app, array $options) {
    $this->addSubscribers($app);
    $this->addControllers($app);
    $this->addPluginViews($app);
  }

  public function addSubscribers(KarambolApp $app) {
      $app['menus']->getMenu(Menus::ADMIN_MAIN)->addSubscriber(new Subscriber\PluginUploaderAdminMenuSubscriber($app));
  }

  public function addControllers(KarambolApp $app) {
    $controllers = [
      'PluginUploader\Controller\UploaderController',
    ];
    foreach($controllers as $controllerClass) {
      $controller = new $controllerClass();
      $controller->bindTo($app);
    }
  }

  public function addPluginViews($app) {
      $twigPaths = $app['twig.path'];
      array_unshift($twigPaths, __DIR__.'/Views');
      $app['twig.path'] = $twigPaths;
  }

}
