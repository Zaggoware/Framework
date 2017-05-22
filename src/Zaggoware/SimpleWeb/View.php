<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\Dynamic;
    use Zaggoware\Helpers\PathHelper;
    use Zaggoware\SimpleWeb\WebPages\StartPage;
    use Zaggoware\SimpleWeb\WebPages\WebViewPage;

    class View implements IView {
        public function __construct(Controller $controller, $viewPath) {
            $this->controller = $controller;
            $this->viewPath = $viewPath;
        }

        /** @var Controller */
        private $controller;

        /** @var string */
        private $viewPath;

        /**
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         * @return string
         */
        public function render(array $viewData, array $tempData, Dynamic $viewBag) {
            $viewPath = PathHelper::normalize($this->viewPath);
            $page = new WebViewPage($viewPath, $viewData, $tempData, $viewBag);

            $viewStart = Site::getConfig()->getViewStart();

            $routes = Site::getRoutes();
            $areaName = null;
            $routes->getDataTokens()->tryGetValue("area", $areaName);
            if (!empty($areaName) && $viewStart[0] === "~") {
                $startPath = PathHelper::getBasePath() . Site::getConfig()->getAreasPath() . "/". $areaName . substr($viewStart, 1);
            } else {
                $startPath = PathHelper::normalize($viewStart);
            }

            if(file_exists($startPath)) {
                $startPage = new StartPage($startPath);
                $startPage->runPage();

                $layoutPath = $startPage->getLayoutPath();

                if(!empty($layoutPath)) {
                    $layoutPath = PathHelper::normalize($layoutPath);

                    if(!file_exists($layoutPath)) {
                        throw new \RuntimeException("Layout '$layoutPath' could not be found.");
                    }

                    $layoutPage = new WebViewPage($layoutPath, $viewData, $tempData, $viewBag);
                    $startPage->setChildPage($layoutPage);
                    $page->addLayoutPage($layoutPage, function() use ($page) {
                            return $page->renderPage();
                        });
                    $page->runPage();
                } else {
                    $startPage->setChildPage($page);
                }

                return $startPage->renderPage();
            }

            return $page->renderPage();
        }
    }
}

 